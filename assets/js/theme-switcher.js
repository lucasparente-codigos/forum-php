document.addEventListener('DOMContentLoaded', () => {
    const themeSwitcher = document.getElementById('theme-switcher');
    const lightIcon = document.getElementById('theme-icon-light');
    const darkIcon = document.getElementById('theme-icon-dark');
    
    // 1. Get user preference from local storage, or system preference
    let currentTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    // 2. Apply the theme on initial load
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);
    
    // 3. Add click event listener to the switcher button
    themeSwitcher.addEventListener('click', () => {
        // Toggle the theme
        currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        
        // Apply the new theme
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        // Save preference to local storage
        localStorage.setItem('theme', currentTheme);
        
        // Update the icon
        updateIcon(currentTheme);
    });

    // 4. Function to update the icon based on the theme
    function updateIcon(theme) {
        if (theme === 'dark') {
            lightIcon.classList.add('hidden');
            darkIcon.classList.remove('hidden');
        } else {
            lightIcon.classList.remove('hidden');
            darkIcon.classList.add('hidden');
        }
    }
});
