// server/index.js
const express = require('express');
const app = express();
const http = require('http');
const server = http.createServer(app);
const { Server } = require("socket.io");

const io = new Server(server, {
  cors: {
    origin: "*", // Très important pour autoriser les autres machines
    methods: ["GET", "POST"]
  }
});

// Ton code socket.io ici...

// Lancement du serveur
server.listen(3000, '0.0.0.0', () => {
  console.log("Serveur opérationnel sur http://172.20.10.5:3000");
});