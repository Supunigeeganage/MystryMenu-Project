function searchRecipes(searchTerm, isProfile = false, isSavedRecipes = false) {
    fetch(`../../backend/searchRecipes.php?query=${encodeURIComponent(searchTerm)}&isProfile=${isProfile}&isSavedRecipes=${isSavedRecipes}`)
        .then(response => response.json())
        .then(data => {
            const recipesSection = document.querySelector('.recipes, .recipe-container');
            recipesSection.innerHTML = '';

            if (data.status === 'success' && data.recipes.length > 0) {
                data.recipes.forEach(recipe => {
                    const recipeElement = document.createElement('div');
                    recipeElement.className = 'recipe';
                    if (isProfile) {
                        recipeElement.innerHTML = `
                            <img src="${recipe.image ? '../../backend' + recipe.image : '../image/recipe1.jpeg'}" 
                                 alt="${recipe.name}"
                                 onclick="viewRecipe(${recipe.recipe_id})"
                                 style="cursor: pointer;"
                                 onerror="this.src='../image/recipe1.jpeg'">
                            <div class="recipe-details">
                                <i class="fas fa-edit edit-icon" onclick="editRecipe(${recipe.recipe_id})"></i>
                                <i class="fas fa-share-alt share-icon" onclick="shareRecipe(${recipe.recipe_id})"></i>
                                <i class="fas fa-trash-alt delete-icon" onclick="deleteRecipe(${recipe.recipe_id})"></i>
                                <span style="font-weight: bold;">${recipe.name}</span>
                            </div>
                        `;
                    } else {
                        recipeElement.innerHTML = `
                            <img src="${recipe.image ? '../../backend' + recipe.image : '../image/recipe1.jpeg'}" 
                                 alt="${recipe.name}"
                                 onclick="viewRecipe(${recipe.recipe_id})"
                                 style="cursor: pointer;"
                                 onerror="this.src='../image/recipe1.jpeg'">
                            <div class="recipe-info">
                                <i class="fas fa-share-alt share-icon" onclick="shareRecipe(${recipe.recipe_id})"></i>
                                <span style="font-weight: bold;">${recipe.name}</span>
                            </div>
                        `;
                    }
                    recipesSection.appendChild(recipeElement);
                });
            } else {
                recipesSection.innerHTML = '<p>No recipes found matching your search.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            recipesSection.innerHTML = '<p>Error searching recipes.</p>';
        });
} 