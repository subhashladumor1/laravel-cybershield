<?php

namespace CyberShield\Logging;

class LogManager
{
    protected LogChannelResolver $channelResolver;
    protected LogContextBuilder $contextBuilder;
    protected LogFormatter $formatter;
    protected LogWriter $writer;

    public function __construct(
        LogChannelResolver $channelResolver,
        LogContextBuilder $contextBuilder,
        LogFormatter $formatter,
        LogWriter $writer
    ) {
        $this->channelResolver = $channelResolver;
        $this->contextBuilder = $contextBuilder;
        $this->formatter = $formatter;
        $this->writer = $writer;
    }

    /**
     * Handle security log logic.
     *
     * @param string $channel
     * @param array $data
     * @param string $level
     * @return void
     */
    public function handle(string $channel, array $data, string $level): void
    {
        if (!$this->channelResolver->isEnabled($channel)) {
            return;
        }

        $context = $this->contextBuilder->build($data);
        $formatted = $this->formatter->format($context, $level);
        
        $this->writer->write($channel, $formatted);
    }
}
