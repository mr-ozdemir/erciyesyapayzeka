from moderation.profanity_filter import ProfanityFilter
from moderation.toxicity_detector import ToxicityDetector
from moderation.spam_filter import SpamFilter
from moderation.safety_rules import SafetyRules
from moderation.message_validator import MessageValidator


class ModerationChain:
    def __init__(self):
        self.steps = [
            MessageValidator(),
            ProfanityFilter(),
            ToxicityDetector(),
            SafetyRules(),
            SpamFilter(),
        ]

    def check(self, text: str, user_id: str = None):
        """
        Bir adım sorun dönerse hemen sonucu döner.
        user_id parametresi spam kontrolü için kullanılabilir.
        """
        for step in self.steps:
            # MessageValidator sadece text alır
            if isinstance(step, MessageValidator):
                result = step.check(text)
            else:
                # Diğer moderation adımları user_id de alabilir
                result = step.check(text, user_id) if user_id else step.check(text)
            
            if result is not None:
                return result
        return None
