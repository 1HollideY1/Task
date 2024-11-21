db = db.getSiblingDB('auth_db');

if (!db.getCollectionNames().includes('users')) {
    db.createCollection('users');
}

db.users.createIndex({ "username": 1 }, { unique: true });

db.users.drop();

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

db.users.insertMany(users);
db.users.find().forEach(printjson);