```mermaid
erDiagram
    User {
        INT user_id PK
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
        INT  recipe_id PK
        VARCHAR name
        enum type "Breakfast, Lunch, Dinner, Vegetarian, Sweets, Drinks, Cakes"
        TEXT ingredient
        TEXT method
        VARCHAR image
        UUID user_id FK
    }
    
    Comments {
        INT commentid PK
        UUID user_id FK
        UUID recipe_id FK
        TEXT comment
    }
    
    SaveRecipe {
        INT recipe_id FK
        INT user_id FK
        timestamp saved_at
    }
    
    User ||--o{ Comments : "can give"
    User ||--o{ SaveRecipe : "can save"
    User ||--o{ Recipe : "create"
    Recipe ||--o{ Comments : "can have"
    Recipe ||--o{ SaveRecipe : "can be saved"
