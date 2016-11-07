<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Text;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Email Pipe
 *
 * Sends an email with a dump of the data in its current state.
 * Chain with other Pipes to convert data before it reaches this
 * Pipe if you want to - just as an example - create a proper
 * email body text containing a nice representaton of the data.
 */
class EmailPipe extends AbstractPipe implements PipeInterface, ViewAwarePipeInterface  {

    use ViewAwarePipeTrait;

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var mixed
	 */
	protected $recipient;

	/**
	 * @var mixed
	 */
	protected $sender;

    /**
     * @var string|NULL
     */
    protected $body = NULL;

    /**
     * @var string|NULL
     */
    protected $bodySection = NULL;

	/**
	 * @return FieldInterface[]
	 */
	public function getFormFields() {
		$fields = parent::getFormFields();
		$fields['subject'] = Input::create(array('type' => 'Input'))
			->setName('subject');
		$fields['body'] = Text::create(array('type' => 'Text'))
			->setName('body');
		$fields['receipent'] = Input::create(array('type' => 'Input'))
			->setName('recipient');
		$fields['sender'] = Input::create(array('type' => 'Input'))
			->setName('sender');
		return $fields;
	}

	/**
	 * @param string $recipient
	 * @return EmailPipe
	 */
	public function setRecipient($recipient) {
		$this->recipient = $recipient;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRecipient() {
		return $this->recipient;
	}

	/**
	 * @param string $sender
	 * @return EmailPipe
	 */
	public function setSender($sender) {
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @param string $subject
	 * @return EmailPipe
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @param string|NULL $body
	 * @return EmailPipe
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getBody() {
		return $this->body;
	}

    /**
     * @return NULL|string
     */
    public function getBodySection()
    {
        return $this->bodySection;
    }

    /**
     * @param NULL|string $bodySection
     */
    public function setBodySection($bodySection)
    {
        $this->bodySection = $bodySection;
    }

    /**
	 * @param mixed $data
	 * @return mixed
	 * @throws Exception
	 */
	public function conduct($data) {
		try {
			$message = $this->prepareEmail($data);
			$this->sendEmail($message);
		} catch (\Swift_RfcComplianceException $error) {
			throw new Exception($error->getMessage(), $error->getCode());
		}
		return $data;
	}

	/**
	 * @param string $data
	 * @return MailMessage
	 */
	protected function prepareEmail($data) {
	    if ($this->getBodySection() !== null) {
	        $body = $this->view->renderStandaloneSection($this->getBodySection(), $data);
        } else {
		    $body = $this->getBody();
        }
		$sender = $this->getSender();
		$recipient = $this->getRecipient();
		if (TRUE === is_array($recipient)) {
			list ($recipientAddress, $recipientName) = $recipient;
		} else {
			$recipientAddress = $recipient;
			$recipientName = NULL;
		}
		if (TRUE === is_array($sender)) {
			list ($senderAddress, $senderName) = $sender;
		} else {
			$senderAddress = $sender;
			$senderName = NULL;
		}
		$subject = $this->getSubject();
		if (TRUE === is_string($data)) {
			$body = $data;
		}
		$message = new MailMessage();
		$message->setSubject($subject);
		$message->setFrom($senderAddress, $senderName);
		$message->setTo($recipientAddress, $recipientName);
		$message->setBody($body);
		return $message;
	}

	/**
	 * @param MailMessage $message
	 * @return void
	 */
	protected function sendEmail(MailMessage $message) {
		$message->send();
	}

}
