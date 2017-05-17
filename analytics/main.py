from instagram.client import InstagramAPI


access_token = "5472012573.88bfc5a.d0c8b51769e343cfbd863a8d0185aa35"
client_secret = "2e9f4130bee443a5a58c6754647ac706"
api = InstagramAPI(access_token=access_token, client_secret=client_secret)

print(api.tag('car'))
print(api.tag_search('asia', 4))

print(api.media_search(lat="37.7808851",lng="-122.3948632",distance=1000))

print( api.media_popular())
