
// Image preview functionality
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const newPreview = document.getElementById('new-image-preview');
    // Use whichever preview element exists
    const targetPreview = newPreview || preview;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            targetPreview.src = e.target.result;
            targetPreview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Ingredient management
function addIngredient() {
    const container = document.getElementById('ingredients-list');
    const newRow = document.createElement('div');
    newRow.className = 'ingredient-row';
    newRow.innerHTML = `
        <input type="text" name="ingredients[]" placeholder="Ingredient" required>
        <input type="text" name="quantities[]" placeholder="Quantity" required>
        <input type="text" name="units[]" placeholder="Unit" required>
        <button type="button" class="btn btn-remove" onclick="removeIngredient(this)">Remove</button>
    `;
    container.appendChild(newRow);
}

function removeIngredient(button) {
    const row = button.parentElement;
    if (document.querySelectorAll('.ingredient-row').length > 1) {
        row.remove();
    } else {
        alert('Recipe must have at least one ingredient!');
    }
}

// Step management
function addStep() {
    const container = document.getElementById('steps-list');
    const newRow = document.createElement('div');
    newRow.className = 'step-row';
    newRow.innerHTML = `
        <textarea name="steps[]" placeholder="Step description" required></textarea>
        <button type="button" class="btn btn-remove" onclick="removeStep(this)">Remove</button>
    `;
    container.appendChild(newRow);
}

function removeStep(button) {
    const row = button.parentElement;
    if (document.querySelectorAll('.step-row').length > 1) {
        row.remove();
    } else {
        alert('Recipe must have at least one step!');
    }
}

// Delete recipe confirmation
function confirmDelete(recipeId) {
    if (confirm('Are you sure you want to delete this recipe? This action cannot be undone.')) {
        window.location.href = 'delete_recipe.php?id=' + recipeId;
    }
}

// Form validation
function validateRecipeForm() {
    const title = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const prepTime = document.getElementById('prep_time').value.trim();
    const cookTime = document.getElementById('cook_time').value.trim();
    const servings = document.getElementById('servings').value;
    
    if (title.length < 3) {
        alert('Recipe title must be at least 3 characters long');
        return false;
    }
    
    if (description.length < 10) {
        alert('Please provide a more detailed description');
        return false;
    }
    
    if (!prepTime || !cookTime) {
        alert('Please specify both preparation and cooking times');
        return false;
    }
    
    if (servings < 1) {
        alert('Number of servings must be at least 1');
        return false;
    }
    
    return true;
}