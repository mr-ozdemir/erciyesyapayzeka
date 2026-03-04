# main.py
import chainlit as cl
from chat_handler import ChatHandler

chat = ChatHandler()

@cl.set_starters
async def set_starters():
    return [
        cl.Starter(label="Etkinlikler", message="Bu yıl hangi etkinlikler var?"),
        cl.Starter(label="Projeler", message="Hangi projelerde çalışıyorsunuz?"),
        cl.Starter(label="Üyelik", message="Nasıl üye olabilirim?"),
    ]

@cl.on_message
async def on_message(message: cl.Message):
    await chat.handle_message(message)

if __name__ == "__main__":
    print("Run with: chainlit run main.py -w")
