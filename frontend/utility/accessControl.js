const protectedPages = {
    'dashboard.html': ['admin', 'user'],
    'profile.html': ['admin', 'user'],
    'add_recipe.html': ['admin', 'user'],
    'edit_recipe.html': ['admin', 'user'],
    'edit_profile.html': ['admin', 'user'],
    'save_recipe.html': ['admin', 'user'],
    'share_recipe.html': ['admin', 'user'],
    'recipeManagment.html': ['admin'],
    'userManagement.html': ['admin'],
    'poisonousRecipes.html': ['admin']
};

// Check user has the access and if not redirect to unauthorized page
function checkAccess(currentPage, userType) {
    if (protectedPages[currentPage] && !protectedPages[currentPage].includes(userType)) {
        window.location.href = '../utility/notAuthorized.html';
    }
}

// Extract the current page name from the URL
function getCurrentPage() {
    const path = window.location.pathname;
    const page = path.substring(path.lastIndexOf('/') + 1);
    return page || 'login.html';
}

// Check access on page load
document.addEventListener('DOMContentLoaded', () => {
    const currentPage = getCurrentPage();
    console.log('Current page:', currentPage);
    
    const publicPages = ['login.html', 'main.html', 'signup.html', '', 'notAuthorized.html'];
    
    // If not a public page must be logged in
    if (!publicPages.includes(currentPage)) {
        const userType = sessionStorage.getItem('userType');
        const userId = sessionStorage.getItem('userId');
        
        // if Not logged in - redirect to login
        if (!userType || !userId) {
            console.log('No valid session - redirecting to login');
            sessionStorage.clear();
            window.location.href = '../html/login.html';
            return;
        }

        // Check if page needs specific permissions
        if (protectedPages[currentPage]) {
            if (!protectedPages[currentPage].includes(userType)) {
                console.log('Unauthorized access - redirecting');
                window.location.href = '../utility/notAuthorized.html';
                return;
            }
        }
    }
});
