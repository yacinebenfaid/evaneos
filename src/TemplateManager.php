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


        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $this->applicationContext->getCurrentUser();

        if($this->hasPlaceHolder(PlaceHolders::USER_FIRST_NAME, $text)) {
            $text = $this->replacePlaceHolder(
                $text,
                PlaceHolders::USER_FIRST_NAME,
                $user->getFirstName()
            );
        }

        return $text;
    }

    private function renderQuote(Quote $quote, string $text): string
    {
        $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        if ($this->hasPlaceHolder(PlaceHolders::QUOTE_SUMMARY_HTML, $text)) {
            $text = $this->replacePlaceHolder(
                $text,
                PlaceHolders::QUOTE_SUMMARY_HTML,
                Quote::renderHtml($quote)
            );
        }

        if ($this->hasPlaceHolder(PlaceHolders::QUOTE_SUMMARY, $text)) {
            $this->replacePlaceHolder(
                $text,
                PlaceHolders::QUOTE_SUMMARY,
                Quote::renderText($quote));
        }

        if ($this->hasPlaceHolder(PlaceHolders::QUOTE_DESTINATION_NAME, $text)) {
            $this->replacePlaceHolder($text, PlaceHolders::QUOTE_DESTINATION_NAME, $destinationOfQuote->countryName);
        }

        if ($this->hasPlaceHolder(PlaceHolders::QUOTE_DESTINATION_LINK, $text) ) {
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            if ($destination) {
                $text = $this->replacePlaceHolder(
                    $text,
                    PlaceHolders::QUOTE_DESTINATION_LINK,
                    $usefulObject->url . '/' . $destination->countryName . '/quote/' . $quote->id
                    );
            } else {
                $text = $this->replacePlaceHolder($text, PlaceHolders::QUOTE_DESTINATION_LINK, '');
            }
        }

        return $text;
    }

    private function hasPlaceHolder(string $placeHolder, string $text)
    {
        return strpos($text, $placeHolder) !== false;
    }

    private function replacePlaceHolder(string $text, string $placeHolderToReplace, string $replacedBy)
    {
        return str_replace($placeHolderToReplace, $replacedBy, $text);
    }
}
