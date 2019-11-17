<?php

class TemplateManager
{
    /** @var ApplicationContext */
    private $applicationContext;

    public function getTemplateComputed(Template $template, array $data)
    {
        $this->applicationContext = ApplicationContext::getInstance();

        if (!$template) {
            throw new \RuntimeException('no template given');
        }

        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $this->applicationContext->getCurrentUser();
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        return $template->renderFromExistingTemplate($template, $user, $quote);
    }
}
