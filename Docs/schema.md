```mermaid
erDiagram
    User {
        UUID user_id PK
        VARCHAR firstName
        VARCHAR lastName
        enum gender "Male, Female"
        enum profession "Chef, Baker, Cake Decorator, Bartender"
        VARCHAR email "user email"
        VARCHAR password
        enum usertype "Admin, User"
        VARCHAR user_pic
    }
    
    Recipe {
        UUID recipe_id PK
        VARCHAR name
        enum type "Breakfast, Lunch, Dinner, Vegetarian, Sweets, Drinks, Cakes"
        TEXT ingredient
        TEXT method
        VARCHAR image
        UUID user_id FK
    }
    
    Comments {
        UUID commentid PK
        UUID user_id FK
        UUID recipe_id FK
        TEXT comment
    }
    
    SaveRecipe {
        UUID save_id PK
        UUID recipe_id FK
        UUID user_id FK
    }
    
    User ||--o{ Comments : "can give"
    User ||--o{ SaveRecipe : "can save"
    User ||--o{ Recipe : "create"
    Recipe ||--o{ Comments : "can have"
    Recipe ||--o{ SaveRecipe : "can be saved"
