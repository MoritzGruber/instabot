import Services.Content.Comments as Comments
import unicodedata

teststring = Comments.getComments('car', 2, 6)
# teststring = unicode(Comments.getComments('car', 2, 6), 'utf-8')
# teststring = str(Comments.getComments('car', 2, 6), 'utf-8')

#encode it with string escape

print (teststring)