import sqlite3
from typing import List, Dict, Any
from datetime import datetime



DB_FILE = "chatbot_data.db"

MAX_DAILY_MESSAGES = 50

# ------------------------------
# DATABASE (unchanged)
# -----------------------------
class DatabaseManager:
    def __init__(self, db_file: str = DB_FILE):
        self.db_file = db_file
        self._init_db()

    def _conn(self):
        return sqlite3.connect(self.db_file, check_same_thread=False)

    def _init_db(self):
        c = self._conn()
        cur = c.cursor()
        cur.execute('''
            CREATE TABLE IF NOT EXISTS conversations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                chat_id TEXT NOT NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                user_message TEXT NOT NULL,
                bot_response TEXT NOT NULL,
                category TEXT
            )
        ''')
        cur.execute('''
            CREATE TABLE IF NOT EXISTS daily_limits (
                session_id TEXT NOT NULL,
                date TEXT NOT NULL,
                message_count INTEGER DEFAULT 0,
                PRIMARY KEY (session_id, date)
            )
        ''')
        c.commit()
        c.close()

    def save_conversation(self, session_id: str, chat_id: str, user_msg: str, bot_response: str, category: str):
        c = self._conn()
        cur = c.cursor()
        cur.execute('''
            INSERT INTO conversations (session_id, chat_id, user_message, bot_response, category)
            VALUES (?, ?, ?, ?, ?)
        ''', (session_id, chat_id, user_msg, bot_response, category))
        c.commit()
        c.close()

    def get_chat_list(self, session_id: str) -> List[Dict[str, Any]]:
        c = self._conn()
        cur = c.cursor()
        cur.execute('''
            SELECT chat_id, MIN(timestamp) as start_time, MAX(timestamp) as last_time, COUNT(*) as message_count,
                   SUBSTR(user_message,1,60) as preview
            FROM conversations
            WHERE session_id = ?
            GROUP BY chat_id
            ORDER BY last_time DESC
            LIMIT 50
        ''', (session_id,))
        rows = cur.fetchall()
        c.close()
        return [
            {'chat_id': r[0], 'start_time': r[1], 'last_time': r[2], 'message_count': r[3], 'preview': r[4]}
            for r in rows
        ]

    def get_chat_messages(self, chat_id: str) -> List[Dict[str, Any]]:
        c = self._conn()
        cur = c.cursor()
        cur.execute('''
            SELECT timestamp, user_message, bot_response, category
            FROM conversations
            WHERE chat_id = ?
            ORDER BY timestamp ASC
        ''', (chat_id,))
        rows = cur.fetchall()
        c.close()
        return [{'timestamp': r[0], 'user_message': r[1], 'bot_response': r[2], 'category': r[3]} for r in rows]

    def check_daily_limit(self, session_id: str) -> bool:
        today = datetime.now().strftime("%Y-%m-%d")
        c = self._conn()
        cur = c.cursor()
        cur.execute('SELECT message_count FROM daily_limits WHERE session_id = ? AND date = ?', (session_id, today))
        r = cur.fetchone()
        c.close()
        if r:
            return r[0] < MAX_DAILY_MESSAGES
        return True

    def increment_daily_count(self, session_id: str):
        today = datetime.now().strftime("%Y-%m-%d")
        c = self._conn()
        cur = c.cursor()
        cur.execute('''
            INSERT INTO daily_limits(session_id, date, message_count)
            VALUES (?, ?, 1)
            ON CONFLICT(session_id, date) DO UPDATE SET message_count = message_count + 1
        ''', (session_id, today))
        c.commit()
        c.close()

    def get_daily_count(self, session_id: str) -> int:
        today = datetime.now().strftime("%Y-%m-%d")
        c = self._conn()
        cur = c.cursor()
        cur.execute('SELECT message_count FROM daily_limits WHERE session_id = ? AND date = ?', (session_id, today))
        r = cur.fetchone()
        c.close()
        return r[0] if r else 0

db = DatabaseManager()

