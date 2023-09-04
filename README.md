# maltslist
A simple way to keep track of movies and tv shows

## Disclaimer

If you're planning on serving this over the internet you should be using some sort of authentication mechanism like authelia or keycloak.

## Feature table

| Feature | Status |
| --- | --- |
| Creating entries | Yes |
| Editing entries | Yes |
| Deleting entries | Will add later |
| Sorting entries | Yes (Current state is a bit buggy) |
| Static read only site running on a different port | Will add later | 
| Mobile device support | Yes |
| TMDB API integration | Yes |
| anilist.co API integration | Will add later |
| Logging | Will add later |
| Authentication | No |
| API | Will add later |
| Docker dev environment | Will add later |
| CI and Releases | Will add later |

## Usage

### Obtain a TMDB API key

Read more [here](https://developer.themoviedb.org/docs/getting-started)

### Set your environment variables in .env

```
DATABASE=maltslist-sample.sqlite3
TMDB_API_KEY=tokengoeshere
TMDB_INCLUDE_ADULT=true
```

### Set permissions for the db folder and sqlite3 database

```
chown -R 65534:65534 db
```

### Build the container

```
docker compose build
```

### Run the container

```
docker compose up -d
```

## Known issues

* If every list type isn't populated sorting might not work properly

## Screenshots

<img src="https://github.com/monkhaze/maltslist/assets/6921039/9e69d654-ec7d-42f1-adb2-7d7b9f91731b" height="50%">
<img src="https://github.com/monkhaze/maltslist/assets/6921039/a54f385b-41b1-4f03-bcf7-cec783cb2c1d" height="50%">
