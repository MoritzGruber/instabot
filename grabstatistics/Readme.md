# grabstatistics

Grabs different data from a Instagram account:

* timestamp
* username
* follower_count
* following_count
* media_count
* usertags_count
* feed_items
* likes
* comments

## Configuration

Open statistics.php and add a parser account: $username = 'xxx'; and $password = 'xxx';

## Start

```python main.py tr3ndfood```

Use carefully on users with a lot of posts (1000+), long duration loading times and potential ban of the parser account, because of to many API calls: about one for each ~18 posts.

## Results

The received data gets written into phpapi/resources/userinformation.csv.