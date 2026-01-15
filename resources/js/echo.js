/**
 * Laravel Echo Configuration for Reverb WebSocket
 * 
 * This file initializes Laravel Echo to connect to the Reverb WebSocket server
 * for real-time updates from devices.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally for Echo
window.Pusher = Pusher;

// Initialize Echo with Reverb configuration
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

// Log connection status (for debugging)
// Connection Status Handling
// Connection Status Handling
const updateStatus = (status, color, message) => {
    const indicator = document.getElementById('websocket-status');
    if (indicator) {
        indicator.style.display = 'block';
        indicator.className = `fixed bottom-4 right-4 w-3 h-3 rounded-full ${color} z-50 transition-colors duration-300 shadow-md`;
        indicator.title = `WebSocket: ${message}`;
    }
    
    if (import.meta.env.DEV) {
        console.log(`[WebSocket] ${status}: ${message}`);
    }
};

window.Echo.connector.pusher.connection.bind('connected', () => {
    updateStatus('connected', 'bg-green-500', 'Connected');
});

window.Echo.connector.pusher.connection.bind('unavailable', () => {
    updateStatus('unavailable', 'bg-red-500', 'Disconnected (Unavailable)');
});

window.Echo.connector.pusher.connection.bind('failed', () => {
    updateStatus('failed', 'bg-red-600', 'Connection Failed');
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    updateStatus('disconnected', 'bg-gray-400', 'Disconnected');
});

window.Echo.connector.pusher.connection.bind('connecting', () => {
    updateStatus('connecting', 'bg-yellow-400', 'Connecting...');
});

export default window.Echo;
