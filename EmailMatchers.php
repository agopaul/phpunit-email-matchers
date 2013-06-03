<?php

/**
 * EmailMatchers.php
 * @author Paolo Agostinetto <paul.ago@gmail.com>
 * @filesource
 * @since 1.0.0
 * @version 1.0.0
 */
 
/**
 * Assertion helpers for PHPUnit and Zend Framework 1.12+
 * 
 * @author Paolo Agostinetto <paul.ago@gmail.com>
 */
abstract class PHPUnitExtensions_EmailMatchers extends Zend_Test_PHPUnit_ControllerTestCase {
	
	/**
	 * Return the Zend_Mail generated emails path
	 * @author Paolo Agostinetto <paul.ago@gmail.com>
	 */
	abstract public function getMailPath();
	
	/**
	 * Get the generated emails
	 * @return array
	 * @author Paolo Agostinetto <paul.ago@gmail.com>
	 */
	public function getGeneratedEmails(){
		
		$emails = array();
		foreach(new DirectoryIterator($this->getMailPath()) as $node){			
			if($node->isFile() && strpos($node->getBasename(), "ZendMail_") === 0){
				
				$emails[] = new Zend_Mail_Message_File(array(
					'file' => $node->getPathname()
				)); 
			}
		}
		
		return $emails;
	}
	
	/**
	 * Email count assertion
	 * 
	 * @param int $expected
	 * @author Paolo Agostinetto <paul.ago@gmail.com>
	 */
	public function assertEmailSentCount($expected){
		
		$emails = $this->getGeneratedEmails();
		$count = count($emails);
		
		$this->assertEquals($expected, $count, "Expected $expected emails, but $count found");
	}
	
	/**
	 * Checks email are sent with specific header properties
	 * 
	 * @param string $to Recipient
	 * @param string $cc
	 * @param string $ccn
	 * @param string $subjectRegexp Subject regexp
	 * @author Paolo Agostinetto <paul.ago@gmail.com>
	 */
	public function assertEmailSentByRecipient($to, $cc = null, $ccn = null, $subjectRegexp = null){
		
		$emails = $this->getGeneratedEmails();
		$found = false;
		foreach($emails as $email){
			/* @var $email Zend_Mail_Message_File */
			
			$emailTo = $email->getHeader("to");
			$emailCc = $email->headerExists("cc") ? $email->getHeader("cc") : null;
			$emailCcn = $email->headerExists("ccn") ? $email->getHeader("ccn") : null;
			
			if($to == $emailTo){
				$found = true;
				
				$this->assertEquals($emailCc, $cc, "Email field CC [$emailCc] doesn't match [$cc]");
				$this->assertEquals($emailCcn, $ccn, "Email field CCN [$emailCcn] doesn't match [$ccn]");
				
				if($subjectRegexp !== null)
					$this->assertRegExp($subjectRegexp, $email->getHeader("subject"), "Subject [".$email->getHeader("subject")."] doesn't contains [$subjectRegexp]'");
			}
		}
		
		if(!$found)
			$this->fail("Email to '$to' not sent");
	}
	
	/**
	 * Checks email are sent with specific subject
	 * 
	 * @param string $subjectRegexp Subject regexp
	 * @param string $to
	 * @param string $cc
	 * @param string $ccn
	 * @author Paolo Agostinetto <paul.ago@gmail.com>
	 */
	public function assertEmailSentBySubject($subjectRegexp, $to = null, $cc = null, $ccn = null){
		
		$emails = $this->getGeneratedEmails();
		$found = false;
		foreach($emails as $email){
			/* @var $email Zend_Mail_Message_File */
			
			$emailSubject = $email->getHeader("subject");
			$emailTo = $email->getHeader("to");
			$emailCc = $email->headerExists("cc") ? $email->getHeader("cc") : null;
			$emailCcn = $email->headerExists("ccn") ? $email->getHeader("ccn") : null;
			
			if(preg_match($subjectRegexp, $emailSubject)){
				$found = true;
				
				$this->assertEquals($emailTo, $to, "Email field CC [$emailTo] doesn't match [$to]");
				$this->assertEquals($emailCc, $cc, "Email field CC [$emailCc] doesn't match [$cc]");
				$this->assertEquals($emailCcn, $ccn, "Email field CCN [$emailCcn] doesn't match [$ccn]");
			}
		}
		
		if(!$found)
			$this->fail("Email subjects doesn't matches '$subjectRegexp'");
	}
}
