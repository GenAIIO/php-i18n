<?php

namespace GenAI\I18n\Attribute;

/**
 * Marker that satisfies the AttributeProcessor contract for MessagesProcessor.
 *
 * Unlike most processors, MessagesProcessor is file-driven (it reads
 * config/messages/*.ini), not attribute-driven — but every processor must name an
 * attribute class. This is that class; you don't apply it to anything. The real
 * work happens in MessagesProcessor::compile().
 *
 * Build-time only (PHP 8).
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Messages
{
}
