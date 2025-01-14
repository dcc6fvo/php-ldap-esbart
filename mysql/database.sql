-- one time password tokens database
DROP DATABASE IF EXISTS esbart;

CREATE DATABASE esbart;

USE esbart;

DROP TABLE IF EXISTS pw_set_requests;

CREATE TABLE IF NOT EXISTS pw_set_requests (
  id INT NOT NULL AUTO_INCREMENT,
  pass CHAR(16) NOT NULL,
  user_id VARCHAR(20) NOT NULL,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expired TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE (pass),
  PRIMARY KEY (id)
);

DROP USER IF EXISTS 'esbart'@'%';
CREATE USER 'esbart'@'%' identified by 'aenae5Oaboo0SaiTheil8xieGhoo7igi';
GRANT ALL PRIVILEGES ON esbart.* to 'esbart'@'%';