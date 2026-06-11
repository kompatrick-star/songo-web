# 🎮 Projet Songo - Architecture Distribuée

Bienvenue sur le dépôt officiel du **Projet Songo**. Ce projet explore la transformation d'un jeu de stratégie traditionnel en une plateforme numérique moderne, robuste et distribuée.

## 🚀 À propos du projet
Le Songo est un jeu de type *Mancala* exigeant une stratégie rigoureuse. Ce projet se divise en deux étapes majeures :
- **Version 1 (Locale) :** Une simulation réactive (Vue.js) utilisant l'arithmétique modulaire pour gérer la distribution des graines sur un plateau de 14 cases.
- **Version 2 (Distribuée) :** Une architecture temps réel utilisant **Node.js** et **Socket.io** pour permettre à deux joueurs distants de s'affronter, avec synchronisation d'état déterministe et gestion des latences réseau.

## 🛠️ Stack Technique
- **Frontend :** Vue.js 3, Vite, CSS Grid/Flexbox.
- **Backend :** Node.js, Express, Socket.io.
- **Infrastructure :** Ngrok (tunneling), PM2 (process management).
- **Paradigme :** Architecture événementielle (Event-Driven), Optimistic UI.

## 📂 Structure du dépôt
```text
/
├── frontend/        # Interface Vue.js et logique de rendu
├── server/          # Serveur Node.js (Orchestrateur Socket.io)
├── docs/            # Rapport de conception technique complet
└── README.md
