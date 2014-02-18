IrcBot
======

A PHP based IRC bot


BobbaBot/BotJavel is a bot written purely in php using the SmartIRC php library.

It's authored by Bobbzorzen the magnificent and written for use on bsnet.

The main purpose of the bot is to greet people whom are joining the channel and to display the code of conduct for dbwebb.

Current commandlist is as follows
----

1. `!join <channelname>` This command is used to tell the bot to join a channel. Only invokable by trusted users(see ircbot.php Line 3)
2. `!slap <username>` This command uses the /me command to slap the specified user with a *"moist cod"*.
3. `!part` This command forces a /part command by the bot making it very very sad.
4. `!opall` This command tells the bot to try and op in all allowed channels.
5. `!coc <id>` This command fetches the chosen coc(*code of conduct*) from dbwebb.se and prints it in the channel.
6. `!github` This command prints a link to this github repo.


Other functionality
----

1. **Youtube videos**
 Detects when youtube videos are posted and prints their title.

2. **welcome message**
 Prints a welcome message to any who joins the channel.