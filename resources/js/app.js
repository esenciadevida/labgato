import './bootstrap';
import './alpine';
import { registerTours } from './onboarding/tour';
import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm.js';

window.Livewire = Livewire;
Livewire.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerTours);
} else {
    registerTours();
}

document.addEventListener(
    'wheel',
    (event) => {
        const target = event.target;
        if (target instanceof HTMLInputElement && target.type === 'number' && target === document.activeElement) {
            event.preventDefault();
        }
    },
    { passive: false }
);
