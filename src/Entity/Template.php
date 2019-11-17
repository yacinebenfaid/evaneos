<?php

class Template
{
    // todo make it private, but careful it's certainly used somewhere..
    public $id;
    public $subject;
    public $content;

    public function __construct($id, $subject, $content)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function renderFromExistingTemplate(Template $template, User $user, Quote $quote = null)
    {
        //todo still don't get it why we clone a template.
        $template = clone($template);

        $template->subject = $this->computeText($template->subject, $user, $quote);
        $template->content = $this->computeText($template->content, $user, $quote);

        return $template;
    }

    private function computeText($text, User $user, Quote $quote = null)
    {

        if ($quote) {
            $text = $this->renderQuote($quote, $text);
        }

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