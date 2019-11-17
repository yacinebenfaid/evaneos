<?php

class TemplateManager
{
    /** @var ApplicationContext */
    private $applicationContext;

    public function getTemplateComputed(Template $tpl, array $data)
    {
        $this->applicationContext = ApplicationContext::getInstance();

        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote) {
            $text = $this->renderQuote($quote, $text);
        }

        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $this->applicationContext->getCurrentUser();
        if($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

    private function renderQuote(Quote $quote, string $text): string
    {
        $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        if ($this->hasQuoteInText(Quote::SUMMARY_HTML, $text)) {
            $text = $this->replaceQuoteInText(
                $text,
                Quote::SUMMARY_HTML,
                Quote::renderHtml($quote)
            );
        }

        if ($this->hasQuoteInText(Quote::SUMMARY, $text)) {
            $this->replaceQuoteInText(
                $text,
                Quote::SUMMARY,
                Quote::renderText($quote));
        }

        if ($this->hasQuoteInText(Quote::DESTINATION_NAME, $text)) {
            $this->replaceQuoteInText($text, Quote::DESTINATION_NAME, $destinationOfQuote->countryName);
        }

        if ($this->hasQuoteInText(Quote::DESTINATION_LINK, $text) ) {
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            if ($destination) {
                $text = $this->replaceQuoteInText(
                    $text,
                    Quote::DESTINATION_LINK,
                    $usefulObject->url . '/' . $destination->countryName . '/quote/' . $quote->id
                    );
            } else {
                $text = $this->replaceQuoteInText($text, Quote::DESTINATION_LINK, '');
            }
        }

        return $text;
    }

    private function hasQuoteInText(string $quote, string $text)
    {
        return strpos($text, $quote) !== false;
    }

    private function replaceQuoteInText(string $text, string $quoteToReplace, string $replacedBy)
    {
        return str_replace($quoteToReplace, $replacedBy, $text);
    }
}
