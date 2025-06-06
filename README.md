ENGLISH :

GameServer Hub Query (GSHQ) is a lightweight and extensible PHP module designed to fetch real-time game server information (players, map, status, etc.) without using RCON. Compatible with many popular games (Source engine, CS2, Arma 3, Teamspeak 3, and more), GSHQ features a clean structure, responsive layout, and easy integration with any panel or CMS.

⚠️ GSHQ is currently in alpha. It is actively maintained and will be updated regularly based on community feedback and demand.
It is built as a modern alternative to the outdated LGSL (Live Game Server List).

✅ Key Features
- Server status query without RCON
- Native support for many games (CSS, CS:GO, CS2, Arma 3, TS3…)
- Clean and responsive UI
- Optimized previews (icons, map thumbnails)
- Built-in user management and changelog system
- Designed for seamless integration with GameServer Hub

FRANÇAIS :

GameServer Hub Query (GSHQ) est un module PHP léger et extensible permettant de récupérer en temps réel les informations de vos serveurs de jeux (joueurs, carte, statut, etc.) sans RCON. Compatible avec de nombreux jeux (Source, CS2, Arma 3, Teamspeak 3, etc.), GSHQ propose une structure propre, un affichage responsive, et s'intègre facilement à tout panel ou CMS.

⚠️ GSHQ est actuellement en version alpha. Il est activement maintenu et sera mis à jour régulièrement en fonction des retours et des besoins de la communauté.
Il a été conçu comme une alternative moderne à LGSL (Live Game Server List), devenu obsolète.

✅ Fonctionnalités principales
- Requête de statut serveur sans RCON
- Support natif pour de nombreux jeux (CSS, CS:GO, CS2, Arma 3, TS3…)
- Interface moderne et responsive
- Aperçus optimisés (icônes, maps)
- Gestion des utilisateurs et changelog intégré
- Développé pour une intégration parfaite avec GameServer Hub

----------------------------------------------------------------------------------------

⚠️ Important Notice About Palworld
GSH Integration Status: Temporarily Disabled I’ve spent several days trying to properly integrate Palworld into GameServer Hub (GSH), aiming for the same level of support provided for all other games.
My goal was simple: allow basic server information to be displayed (hostname, number of online players, etc.) for real-time monitoring — just like most multiplayer games do.
Unfortunately, nothing works as of today.

Why: 

- Their official wiki is incomplete, confusing, and in some cases unusable.
- The former RCON interface has been removed without offering a stable replacement.
- The new REST API, despite being officially announced, is:
- Poorly documented, labeled as “experimental”,
- Has no clear instructions for activation,
- And most importantly, is non-functional in its current state (no port open, no network response, not even locally — logs are silent).

Not a lack of skill or effort :

I’ve tried everything on the server side: configuration, networking, SSH debugging, cURL, local and remote testing.
But we can’t build anything serious when the developers provide no usable tools for community server admins.

Recommendation :

Until Palworld developers provide a fully functional and properly documented API, I do not recommend integrating Palworld into GSH.
If things improve in the future, I’ll update this integration accordingly with full support.

Final Word :

To those who, like me, are trying to build reliable tools despite these technical limitations — stay strong.



