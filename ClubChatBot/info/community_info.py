import os

class CommunityInfo:

    @staticmethod
    def get_info():
        # Dosyanın bulunduğu dizini bul
        base_path = os.path.dirname(os.path.abspath(__file__))
        file_path = os.path.join(base_path, "community_info.txt")

        # Dosyayı oku
        with open(file_path, "r", encoding="utf-8") as file:
            return file.read()