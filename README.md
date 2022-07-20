# maltslist
A simple way to keep track of movies and tv shows.

<img src="/screenshot.png" width="50%">

### Features
* No Auth (Use SSO solution like Authelia instead)
* Static version of the list alongside dynamic one
* CSV Export
* Syslog style logging

### Planned features
* An info button next to a list item that would open a modal and show IMDB info about the entry
* Dark mode (Will probably require switching to a different css framework)

### TODO
* Cleanup/Refactor
* Sanitize user input
* Add more functions (A lot of re-usable code currently that is duplicated)
* Better error handling
* Switch away from log4php library (Maybe use Monolog instead)
* Write documentation (At least how to setup)
