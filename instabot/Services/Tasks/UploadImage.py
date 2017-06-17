import logging
import subprocess
from .Task import baseTask


class uploadTask(baseTask):
    def run(self):
        logging.info('T: Uploading a Photo')
        print('T: Uploading a Photo')
        subprocess.check_output(["php", "phpapi/uploadImage.php", self.config['username'], self.config['password']])

