/**
 * Fortune Wheel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const fortuneContainers = document.querySelectorAll('.rae-fortune-container');
    
    fortuneContainers.forEach(container => {
        const spinButton = container.querySelector('.rae-fortune-spin-btn');
        const wheel = container.querySelector('.rae-wheel');
        const form = container.querySelector('.rae-fortune-form');
        
        if (!spinButton || !wheel || !form) return;
        
        spinButton.addEventListener('click', function() {
            const spinning = container.getAttribute('data-spinning') === 'true';
            
            if (spinning) return;
            
            // Mark as spinning
            container.setAttribute('data-spinning', 'true');
            
            // Animation parameters
            const startAngle = 0;
            let targetAngle = (5 * 360); // 5 full rotations
            
            // Add random offset for mobile
            if (window.matchMedia('(max-width: 767px)').matches) {
                targetAngle += 60;
            }
            
            // Reset wheel
            wheel.style.transition = 'none';
            wheel.style.transform = `rotate(${startAngle}deg)`;
            
            // Start spinning animation
            setTimeout(() => {
                wheel.style.transition = 'transform 5s cubic-bezier(0.25, 0.1, 0.25, 1)';
                wheel.style.transform = `rotate(${targetAngle}deg)`;
            }, 10);
            
            // After spin completes (5 seconds)
            setTimeout(() => {
                container.setAttribute('data-spinning', 'false');
                container.classList.add('spin-complete');
                
                // Show form
                form.style.display = 'block';
                
                // Initialize form submission if needed
                if (typeof raeInitForm === 'function') {
                    raeInitForm(form);
                }
            }, 5000);
        });
    });
});
