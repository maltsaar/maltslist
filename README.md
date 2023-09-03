# maltslist
A simple way to keep track of movies and tv shows

## Feature table

| Feature | Status |
| --- | --- |
| Creating entries | Yes |
| Editing entries | Yes |
| Deleting entries | Will add later |
| Sorting entries | Yes (Current state is a bit buggy) |
| Mobile device support | Yes |
| TMDB API integration | Yes |
| anilist.co API integration | Will add later |
| Logging | Will add later |
| Authentication | No |
| API | Will add later |
| Docker dev environment | Will add later |

## Usage

### Set your environment variables in .env

```
DATABASE=maltslist-sample.sqlite3
TMDB_API_KEY=tokengoeshere
TMDB_INCLUDE_ADULT=true
```

### Build the container

```
docker compose build
```

### Run the container

```
docker compose up -d
```

## Current issues

* If every list type isn't populated sorting might not work properly

## Screenshots

<img src="https://github.com/monkhaze/maltslist/assets/6921039/9e69d654-ec7d-42f1-adb2-7d7b9f91731b" height="50%">
<img src="https://github.com/monkhaze/maltslist/assets/6921039/a54f385b-41b1-4f03-bcf7-cec783cb2c1d" height="50%">
