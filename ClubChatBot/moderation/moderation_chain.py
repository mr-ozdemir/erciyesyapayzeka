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

    def check(self, text: str):
        """
        Bir adım sorun dönerse hemen sonucu döner.
        """
        for step in self.steps:
            result = step.check(text)
            if result is not None:
                return result
        return None
