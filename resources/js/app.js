// 1. Initialize backend tools (Axios)
import './bootstrap';

// 2. Import Bootstrap and make it globally available for inline Blade scripts
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// 3. Import and initialize Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();