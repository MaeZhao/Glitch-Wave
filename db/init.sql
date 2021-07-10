--- Users ---

CREATE TABLE users (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL,
	username TEXT NOT NULL UNIQUE,
	password TEXT NOT NULL
);

INSERT INTO users (id, name, username, password) VALUES (1, 'Luis', 'luis', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (2, 'Maria', 'mariamaria', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey


--- Sessions ---

CREATE TABLE sessions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
	session TEXT NOT NULL UNIQUE,
  last_login   TEXT NOT NULL,

  FOREIGN KEY(user_id) REFERENCES users(id)
);

--- Groups ----

CREATE TABLE groups (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL UNIQUE
);

INSERT INTO groups (id, name) VALUES (1, 'admin');


--- Group Membership

CREATE TABLE memberships (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  group_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,

  FOREIGN KEY(group_id) REFERENCES groups(id),
  FOREIGN KEY(user_id) REFERENCES users(id)
);

INSERT INTO memberships (group_id, user_id) VALUES (1, 1); -- User 'musicaluis' is a member of the 'admin' group.


-- Songs table

CREATE TABLE songs (
	id	INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
  title	TEXT NOT NULL,
	album	TEXT NOT NULL,
	artist	TEXT NOT NULL,
	playlist_id	INTEGER,
	rating	INTEGER,
	file_name    TEXT,
  file_ext    TEXT,
  source      TEXT
);
-- Songs seed data:

INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ('Plastic Love', 'Plastic Love', 'Astrophysics', 1, 1, NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'nightwalk', 'new visuals', 'bl00dwave', 2, 3, NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'girl', 'new visuals', 'bl00dwave', 2, 4, NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'cocktail', 'new visuals', 'bl00dwave', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Neon Maze', 'PLASTIC WHATEVER', 'Desired', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Plastic Life', 'PLASTIC WHATEVER', 'Desired', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Android Shelter', 'PLASTIC WHATEVER', 'Desired', 1, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Midnight Dance', 'The Sweetest Dream', 'Desired', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Memories', 'Online Dating', 'Vercetti', 2, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Promises', 'Online Dating', 'Vercetti', 2, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Fly With Me', 'Bae', 'YUNG BAE', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Satisfy', 'Bae', 'YUNG BAE', 2, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Hit Vibes', 'Hit Vibes', 'SAINT PEPSI', 2, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Have Faith', 'Hit Vibes', 'SAINT PEPSI', 1, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Better', 'Hit Vibes', 'SAINT PEPSI', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Around', 'Hit Vibes', 'SAINT PEPSI', NULL, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Cherry Pepsi', 'Hit Vibes', 'SAINT PEPSI', 1, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Sparkle Motion', 'Sparkle Motion', 'Pink Ranger', 2, 5,'Pink Ranger - Sparkle Motion - 01 Sparkle Motion.mp3', '.mp3', 'https://bandcamp.com/' );
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Phantom Machines', 'Sparkle Motion', 'Pink Ranger', NULL, 4, NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Goodnight', 'Sparkle Motion', 'Pink Ranger', 2, ' ', NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Twin Peaks', 'Sparkle Motion', 'Pink Ranger', 2, 2, NULL, NULL, NULL);
INSERT INTO songs (title, album, artist, playlist_id, rating, file_name, file_ext, source) VALUES ( 'Lisa Frank 420 / Modern Computing', 'FLORAL SHOPPE', 'MACINTOSH PLUS', 1, 5, '02 Lisa Frank 420 _ Modern Computing.mp3', '.mp3', 'https://bandcamp.com/' );


-- Playlist table

CREATE TABLE playlists (
	id	INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	author	TEXT,
	title	TEXT NOT NULL
);
-- Playlist seed data:
INSERT INTO playlists ( author, title) VALUES ( 'Luis', 'Glitch Wave Classics');
INSERT INTO playlists ( author, title) VALUES ( 'Luis', 'New Wave');



-- Playlist Song Mapper table

CREATE TABLE mapper (
	id	INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,
	playlist_id	INTEGER NOT NULL,
	song_id	INTEGER NOT NULL
);
-- Playlist Song Mapper  seed data:
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,1);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,22);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,21);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,11);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,6);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,7);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1, 8);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 1,3);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,1);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,21);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,2);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,11);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,10);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,7);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2, 5);
INSERT INTO mapper ( playlist_id, song_id) VALUES ( 2,15);
