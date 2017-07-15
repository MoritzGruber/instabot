import subprocess
import os
from Comments import getUnicodeCommentsSaveToFile

import sys

topic = sys.argv[1]
maxpp = sys.argv[2]
num = sys.argv[3]

getUnicodeCommentsSaveToFile(topic, maxpp, num)