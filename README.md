# instabot
A Instagram bot for educational purposes

## statistics

Returns different information from a Instagram account:

* timestamp
* username
* follower_count
* following_count
* media_count
* usertags_count
* feed_items
* likes
* comments

#### Configuration

Open statistics.php and add a parser account: $username = 'xxx'; and $password = 'xxx';

#### Start

```python Statistics.py tr3ndfood```

Use carefully on users with a lot of posts (1000+), long duration loading times and potential ban of the parser account, because of to many API calls: about one for each ~18 posts.

#### Results

The received data gets written into phpapi/resources/userinformation.csv.

## grabcomments

Grabs different comments for a specified topic.

#### Configuration

Open comments.php and add a parser account: $username = 'xxx'; and $password = 'xxx';

#### Start

```python Comments.py topic maxpp num```

Where topic is your specified hashtag, maxpp is the number of comments grabbed per picture and num is the overall number of comments which get returned.

#### Results

The received data gets written into phpapi/resources/comments.json.