const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const axios = require('axios');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: 'http://localhost:8000', // Your Laravel app URL
        methods: ['GET', 'POST'],
        credentials: true,
    },
});

app.use(express.json());

// Store connected clients with their roles
const clients = new Map();

// Handle Socket.io connections
io.on('connection', (socket) => {
    console.log('Client connected:', socket.id);

    // Authenticate client
    socket.on('authenticate', async ({ token, role }) => {
        try {
            // Verify token with Laravel (adjust endpoint as needed)
            const response = await axios.post('http://localhost:8000/api/verify-token', { token }, {
                headers: { Authorization: `Bearer ${token}` },
            });

            if (response.data.valid && ['waiter', 'reception', 'superadmin'].includes(role)) {
                clients.set(socket.id, { role, user_id: response.data.user_id });
                socket.join(role); // Join role-specific room
                socket.emit('authenticated', { success: true });
            } else {
                socket.emit('authenticated', { success: false, error: 'Invalid role or token' });
                socket.disconnect();
            }
        } catch (error) {
            socket.emit('authenticated', { success: false, error: 'Authentication failed' });
            socket.disconnect();
        }
    });

    socket.on('disconnect', () => {
        clients.delete(socket.id);
        console.log('Client disconnected:', socket.id);
    });
});

// Endpoint for Laravel to trigger events
app.post('/broadcast', (req, res) => {
    const { event, data, roles } = req.body;

    // Broadcast to specific roles
    roles.forEach(role => {
        io.to(role).emit(event, data);
    });

    res.json({ success: true });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Socket.io server running on port ${PORT}`);
});