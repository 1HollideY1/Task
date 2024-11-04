// docker/mongodb/init-mongo.js

db = db.getSiblingDB('auth_db');

// Создаем коллекцию users если она не существует
if (!db.getCollectionNames().includes('users')) {
    db.createCollection('users');
}

// Создаем уникальный индекс по полю username
db.users.createIndex({ "username": 1 }, { unique: true });

// Очищаем коллекцию перед добавлением тестовых данных
db.users.drop();

// Создаем тестовых пользователей
const users = [
    {
        username: "Кирилл",
        login: "kirill",
        password: "user1"
    },
    {
        username: "Олег",
        login: "oleg",
        password: "user2"
    },
    {
        username: "Вика",
        login: "vika",
        password: "user3"
    }
];

// Добавляем пользователей
db.users.insertMany(users);

print("Added users:");
db.users.find().forEach(printjson);