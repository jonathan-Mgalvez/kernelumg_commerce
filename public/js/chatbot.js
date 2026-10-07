// KernelUMG - Script de muestra provisional
document.addEventListener('DOMContentLoaded', () => {
    // Garantiza la existencia del token de sesión para el visitante
    let sessionToken = localStorage.getItem('bot_session_token');
    if (!sessionToken) {
        sessionToken = 'sess_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
        localStorage.setItem('bot_session_token', sessionToken);
    }

    console.log('[KernelUMG Bot] Entorno frontend inicializado con sesión:', sessionToken);
});