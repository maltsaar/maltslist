CREATE TABLE "list" (
	"index"	INTEGER,
	"title"	TEXT,
	"year"	INTEGER,
	"season"	TEXT,
	"score"	INTEGER,
	"progress"	INTEGER,
	"progress_length"	INTEGER,
	"type"	TEXT,
	"rewatch"	INTEGER,
	"favorite"	TEXT,
	"comment"	TEXT,
	"tmdb_id"	INTEGER,
	"tmdb_cover"	TEXT,
	"tmdb_banner"	TEXT,
	"tmdb_description"	TEXT,
	PRIMARY KEY("index" AUTOINCREMENT)
) STRICT;

CREATE TABLE "last-updated" (
    "timestamp" TEXT
) STRICT;
