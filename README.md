<img src="https://esport-cms.net/framework/uploads/esportcms-digital/Capture%20d'%C3%A9cran%202025-06-15%20124627.png" alt="Capture d'écran" style="max-width: 100%; height: auto;">

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

About LGSL and GSHQ
I want to acknowledge the remarkable work of tltneon, the creator of LGSL, who laid the foundation for a very useful tool for the community over many years.
GSHQ is not a rejection or criticism of LGSL, but an attempt to offer a modern, lightweight solution adapted to today’s technologies such as REST APIs.
Every tool has its own approach, and GSHQ simply aims to provide an updated alternative for modern panels and CMS.

----------------------------------------------------------------------------------------

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

A propos de LGSL et GSHQ
Je tiens à souligner le travail remarquable de tltneon, le créateur de LGSL, qui a posé les bases d’un outil très utile pour la communauté pendant de nombreuses années.
GSHQ n’est pas un rejet ou une critique de LGSL, mais une tentative de proposer une solution moderne, plus légère, et adaptée aux technologies actuelles comme les API REST.
Chaque outil a sa méthode propre, et GSHQ souhaite simplement offrir une alternative mise à jour pour les panels et CMS modernes.

----------------------------------------------------------------------------------------

⚠️ Important Notice About Palworld — Integration Status Temporarily Disabled
I spent several days trying to properly integrate Palworld into GameServer Hub (GSH), aiming for the same support level as other games. My goal was simple: display basic server info (hostname, player count, etc.) for real-time monitoring, like most multiplayer games. Unfortunately, nothing works as of now.

Why:

- Their official wiki is incomplete, confusing, and often unusable.
- The former RCON interface was removed without a stable replacement.
- The new REST API, although officially announced, is:
- Poorly documented and labeled “experimental,”
- Has no clear activation instructions,
- And most importantly, is currently non-functional (no open ports, no network response even locally, silent logs).

Not a lack of skill or effort:

I tried everything server-side: config, network, SSH debugging, cURL, local & remote tests. But it’s impossible to build anything reliable without usable tools from the developers.

Recommendation:

Until Palworld developers provide a fully functional, well-documented API, I do not recommend integrating Palworld into GSH. If this improves, I will update the integration accordingly.

Final word:

To those trying to build reliable tools despite these limitations — stay strong.

N/B:

Each query tool uses its own approach. LGSL7 supports Palworld via a legacy method that works for them. GSHQ aims for a modern, clean approach based on REST APIs. Unfortunately, Palworld’s REST API is currently non-functional and poorly documented, making support impossible for GSHQ. This is a technical limitation of Palworld, not GSHQ.
