# Zillowbot
Slash command for Slack to display current mortgage rates, monthly payments, and insurance.

# REQUIREMENTS

- A custom slash command on a Slack team
- A web server running PHP5 with cURL enabled
- A Zillow Web Service Identifier (http://www.zillow.com/howto/api/APIOverview.htm)

# USAGE

- Place the zillowbot.php script on a server running PHP5 with cURL. (Check out Heroku if you do not currently have a web server)
- Set up a new custom slash command on your Slack team: my.slack.com/services/new/slash-commands
- Under "Choose a command", enter whatever you want for the command. I use /zillow.
- Under "URL", enter the URL for the script on your server.
- Leave "Method" set to "Post".
- Enter a short description and usage hint.
- Update the zillowbot.php script with your Zillow Web Service Identifier (zws_id) and slash command's token.
