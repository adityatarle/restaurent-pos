from fastapi import FastAPI, Depends, WebSocket, WebSocketDisconnect, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Dict, Optional

app = FastAPI(title="Restaurant POS Backend")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


class LoginRequest(BaseModel):
    username: str
    password: str


class LoginResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
    role: str


# In-memory demo store for first run; will be replaced with DB models
FAKE_USERS = {
    "waiter1": {"password": "pass", "role": "waiter"},
    "reception1": {"password": "pass", "role": "receptionist"},
    "owner1": {"password": "pass", "role": "owner"},
}


@app.post("/auth/login", response_model=LoginResponse)
async def login(payload: LoginRequest):
    user = FAKE_USERS.get(payload.username)
    if not user or user["password"] != payload.password:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid credentials")
    # demo token
    token = f"demo-token-{payload.username}"
    return LoginResponse(access_token=token, role=user["role"]) 


class Table(BaseModel):
    id: int
    name: str
    status: str  # "empty" | "assigned"
    customer_count: int = 0
    assigned_by: Optional[str] = None


TABLES: Dict[int, Table] = {
    1: Table(id=1, name="T1", status="empty"),
    2: Table(id=2, name="T2", status="empty"),
    3: Table(id=3, name="T3", status="empty"),
}


@app.get("/tables", response_model=List[Table])
async def list_tables():
    return list(TABLES.values())


class AssignTableRequest(BaseModel):
    table_id: int
    customer_count: int
    assigned_by: str


@app.post("/tables/assign", response_model=Table)
async def assign_table(payload: AssignTableRequest):
    table = TABLES.get(payload.table_id)
    if not table:
        raise HTTPException(404, "Table not found")
    if table.status == "assigned":
        raise HTTPException(400, "Table already assigned")
    table.status = "assigned"
    table.customer_count = payload.customer_count
    table.assigned_by = payload.assigned_by
    TABLES[table.id] = table
    await WebSocketManager.broadcast({
        "type": "table_assigned",
        "table": table.model_dump(),
    })
    return table


class VacateTableRequest(BaseModel):
    table_id: int


@app.post("/tables/vacate", response_model=Table)
async def vacate_table(payload: VacateTableRequest):
    table = TABLES.get(payload.table_id)
    if not table:
        raise HTTPException(404, "Table not found")
    table.status = "empty"
    table.customer_count = 0
    table.assigned_by = None
    TABLES[table.id] = table
    await WebSocketManager.broadcast({
        "type": "table_vacated",
        "table": table.model_dump(),
    })
    return table


class MenuCategory(BaseModel):
    id: int
    name: str


class MenuItem(BaseModel):
    id: int
    category_id: int
    name: str
    price: float


CATEGORIES = [
    MenuCategory(id=1, name="Starters"),
    MenuCategory(id=2, name="Main Course"),
    MenuCategory(id=3, name="Beverages"),
]

ITEMS = [
    MenuItem(id=1, category_id=1, name="Soup", price=4.5),
    MenuItem(id=2, category_id=2, name="Pasta", price=8.9),
    MenuItem(id=3, category_id=3, name="Cola", price=2.0),
]


@app.get("/menu/categories", response_model=List[MenuCategory])
async def get_categories():
    return CATEGORIES


@app.get("/menu/items", response_model=List[MenuItem])
async def get_items(category_id: Optional[int] = None):
    if category_id:
        return [i for i in ITEMS if i.category_id == category_id]
    return ITEMS


class OrderItem(BaseModel):
    item_id: int
    quantity: int
    note: Optional[str] = None


class Order(BaseModel):
    id: int
    table_id: int
    items: List[OrderItem]
    status: str  # "open" | "printed" | "cancelled" | "billed"


ORDERS: Dict[int, Order] = {}
ORDER_SEQ = 1


class CreateOrderRequest(BaseModel):
    table_id: int
    items: List[OrderItem]


class PrintRequest(BaseModel):
    order_id: int


@app.post("/orders", response_model=Order)
async def create_order(payload: CreateOrderRequest):
    global ORDER_SEQ
    if TABLES.get(payload.table_id) is None:
        raise HTTPException(404, "Table not found")
    order = Order(id=ORDER_SEQ, table_id=payload.table_id, items=payload.items, status="open")
    ORDERS[ORDER_SEQ] = order
    ORDER_SEQ += 1
    await WebSocketManager.broadcast({
        "type": "order_created",
        "order": order.model_dump(),
    })
    return order


class UpdateOrderRequest(BaseModel):
    items: List[OrderItem]


@app.put("/orders/{order_id}", response_model=Order)
async def update_order(order_id: int, payload: UpdateOrderRequest):
    order = ORDERS.get(order_id)
    if not order:
        raise HTTPException(404, "Order not found")
    if order.status in {"cancelled", "billed"}:
        raise HTTPException(400, "Cannot modify a finalised order")
    order.items = payload.items
    ORDERS[order_id] = order
    await WebSocketManager.broadcast({
        "type": "order_updated",
        "order": order.model_dump(),
    })
    return order


@app.post("/orders/print")
async def print_order(payload: PrintRequest):
    order = ORDERS.get(payload.order_id)
    if not order:
        raise HTTPException(404, "Order not found")
    order.status = "printed"
    ORDERS[order.id] = order
    await save_print_job(order)
    await WebSocketManager.broadcast({
        "type": "order_printed",
        "order": order.model_dump(),
    })
    return {"ok": True}


class CancelOrderRequest(BaseModel):
    order_id: int
    reason: Optional[str] = None


@app.post("/orders/cancel")
async def cancel_order(payload: CancelOrderRequest):
    order = ORDERS.get(payload.order_id)
    if not order:
        raise HTTPException(404, "Order not found")
    order.status = "cancelled"
    ORDERS[order.id] = order
    await WebSocketManager.broadcast({
        "type": "order_cancelled",
        "order": order.model_dump(),
        "reason": payload.reason,
    })
    return {"ok": True}


class BillRequest(BaseModel):
    order_id: int


@app.post("/orders/bill")
async def bill_order(payload: BillRequest):
    order = ORDERS.get(payload.order_id)
    if not order:
        raise HTTPException(404, "Order not found")
    order.status = "billed"
    ORDERS[order.id] = order
    await WebSocketManager.broadcast({
        "type": "order_billed",
        "order": order.model_dump(),
    })
    return {"ok": True}


async def save_print_job(order: Order):
    # Stub print to file; can be replaced by ESC/POS
    from datetime import datetime
    from pathlib import Path

    lines = [
        f"KITCHEN TICKET - ORDER {order.id}",
        f"Table: {order.table_id}",
        "Items:",
    ]
    for oi in order.items:
        item = next((i for i in ITEMS if i.id == oi.item_id), None)
        name = item.name if item else f"Item {oi.item_id}"
        note = f" ({oi.note})" if oi.note else ""
        lines.append(f" - {name} x{oi.quantity}{note}")
    ts = datetime.now().strftime("%Y%m%d-%H%M%S")
    out = "\n".join(lines) + "\n"
    path = Path("/workspace/backend/print_jobs") / f"order_{order.id}_{ts}.txt"
    path.write_text(out)


class WebSocketManager:
    connections: List[WebSocket] = []

    @classmethod
    async def connect(cls, websocket: WebSocket):
        await websocket.accept()
        cls.connections.append(websocket)

    @classmethod
    def disconnect(cls, websocket: WebSocket):
        try:
            cls.connections.remove(websocket)
        except ValueError:
            pass

    @classmethod
    async def broadcast(cls, message: dict):
        to_remove: List[WebSocket] = []
        for ws in cls.connections:
            try:
                await ws.send_json(message)
            except Exception:
                to_remove.append(ws)
        for ws in to_remove:
            cls.disconnect(ws)


@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    await WebSocketManager.connect(websocket)
    try:
        while True:
            await websocket.receive_text()
    except WebSocketDisconnect:
        WebSocketManager.disconnect(websocket)