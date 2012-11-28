CREATE TABLE IF NOT EXISTS users (
	id varchar(40) PRIMARY KEY,
	name varchar(255) NOT NULL,
	points numeric(10,2) NOT NULL DEFAULT 0	
);

CREATE TABLE IF NOT EXISTS statuses (
	id varchar(40) PRIMARY KEY,
	message text NOT NULL,
	user_id varchar(40),
	FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS friends (
	id serial PRIMARY KEY,
	user_id varchar(40),
	friend_id varchar(40),
	FOREIGN KEY(user_id) REFERENCES users(id),
	FOREIGN KEY(friend_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS answers (
	id serial PRIMARY KEY,
	user_id varchar(40),
	status_id varchar(40),
	FOREIGN KEY(user_id) REFERENCES users(id),
	FOREIGN KEY(status_id) REFERENCES statuses(id)
);

ALTER TABLE friends ADD CONSTRAINT unq_user_friend UNIQUE (user_id, friend_id);