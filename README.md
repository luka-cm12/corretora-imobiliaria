corretora-imobiliaria/
├── backend/
│   ├── config/
│   │   ├── database.js
│   │   ├── upload.js
│   │   └── s3.js
│   ├── controllers/
│   │   ├── authController.js
│   │   ├── propertyController.js
│   │   └── imageController.js
│   ├── middlewares/
│   │   ├── authMiddleware.js
│   │   └── cacheMiddleware.js
│   ├── models/
│   │   └── Property.js
│   ├── routes/
│   │   ├── authRoutes.js
│   │   ├── propertyRoutes.js
│   │   └── imageRoutes.js
│   ├── public/
│   │   └── uploads/
│   ├── .env
│   └── server.js
├── frontend/
│   ├── public/
│   │   ├── index.html
│   │   ├── contato.html
│   │   ├── sobre.html
│   │   ├── imoveis/
│   │   │   ├── index.html
│   │   │   └── detalhe.html
│   │   └── private/
│   │       ├── login.html
│   │       ├── dashboard.html
│   │       └── cadastro-imoveis/
│   │           ├── index.html
│   │           ├── novo.html
│   │           └── editar.html
│   └── assets/
│       ├── css/
│       │   ├── style.css
│       │   ├── filters.css
│       │   └── dashboard.css
│       ├── js/
│       │   ├── main.js
│       │   ├── auth.js
│       │   ├── property.js
│       │   └── upload.js
│       └── images/
├── scripts/
│   └── init-db.sql
└── README.md