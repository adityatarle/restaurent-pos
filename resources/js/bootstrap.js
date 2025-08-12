import Echo from 'laravel-echo';
import io from 'socket.io-client';

window.io = io;

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':8080',
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}` // Adjust based on your auth
        }
    }
});