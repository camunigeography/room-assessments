<?php

# Class to create a room risk assessments form

#!# Editability from year-to-year facility


require_once ('frontControllerApplication.php');
class roomAssessments extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'applicationName' => 'Room risk assessment online reporting',
			'div' => strtolower (__CLASS__),
			'tabUlClass' => 'tabsflat',
			'database' => 'roomassessments',
			'table' => 'roomassessments',
			'useCamUniLookup' => true,
			'emailDomain' => 'cam.ac.uk',
			'administrators' => true,
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function assign additional actions
	function actions ()
	{
		# Specify additional actions
		$actions = array (
			'home' => array (
				'description' => 'Submit a room risk assessment',
				'url' => '',
				'tab' => 'Submit room risk assessment',
				'icon' => 'page',
				'authentication' => true,
			),
			'download' => array (
				'description' => 'Download data',
				'url' => 'download/',
				'tab' => 'Download data',
				'icon' => 'disk',
				'administrator' => true,
			),
			'downloadcsv' => array (
				'description' => 'Download data',
				'administrator' => true,
				'export' => true,
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Database structure definition
	public function databaseStructure ()
	{
		return "
			CREATE TABLE IF NOT EXISTS `administrators` (
			  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Username' PRIMARY KEY,
			  `active` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes' COMMENT 'Currently active?',
			  `privilege` enum('Administrator','Restricted administrator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Administrator' COMMENT 'Administrator level'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='System administrators';
			
			CREATE TABLE IF NOT EXISTS `roomassessments` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key' PRIMARY KEY,
			  `room` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `building` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `function` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `occupiers` text COLLATE utf8mb4_unicode_ci NOT NULL,
			  `tidy` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `tidyDetails` text COLLATE utf8mb4_unicode_ci,
			  `fireEscapeSign` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `fireEscapeSignDetails` text COLLATE utf8mb4_unicode_ci,
			  `vdu` enum('','Yes','No','Not sure') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `vduDetails` text COLLATE utf8mb4_unicode_ci,
			  `specialConsiderations` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `specialConsiderationsDetails` text COLLATE utf8mb4_unicode_ci,
			  `highShelves` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `highShelvesDetails` text COLLATE utf8mb4_unicode_ci,
			  `shelvesFixed` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `shelvesFixedDetails` text COLLATE utf8mb4_unicode_ci,
			  `furniture` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `furnitureDetails` text COLLATE utf8mb4_unicode_ci,
			  `ppe` enum('','Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
			  `ppeDetails` text COLLATE utf8mb4_unicode_ci,
			  `chemicals` text COLLATE utf8mb4_unicode_ci,
			  `comments` text COLLATE utf8mb4_unicode_ci,
			  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Automatic timestamp'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Table of room assessments';
			
			CREATE TABLE IF NOT EXISTS `settings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)' PRIMARY KEY ,
			  `recipientEmail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recipient@example.com' COMMENT 'Recipient email'
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Settings';
		";
	}
	
	
	# Additional initialisation
	function main ()
	{
		
	}
	
	
	# Welcome screen
	function home ()
	{
		# Start the HTML
		$html = '';
		
		# Create a new form
		$form = new form (array (
			'div' => false,
			'displayRestrictions' => false,
			'nullText' => '',
			'formCompleteText' => $this->tick . ' Thank you for your submission.',
			'autofocus' => true,
			'display' => 'template',
			'displayTemplate' => '<p>{[[PROBLEMS]]}</p>' . $this->template () . '<p>{[[SUBMIT]]}</p>',
			'databaseConnection' => $this->databaseConnection,
			'rows' => 3,
			'cols' => 60,
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $this->settings['table'],
			'intelligence' => true,
			'attributes' => array (
				'name' => array ('default' => $this->userName, 'editable' => (!$this->userName), ),
				'email' => array ('default' => $this->userEmail, 'editable' => false, ),
			),
		));
		$form->setOutputEmail ($this->settings['recipientEmail'], $this->settings['administratorEmail'], 'Room risk assessment for room {room}', NULL, 'email');
		$form->setOutputConfirmationEmail ('email', $this->administratorEmail, 'Room risk assessment for room {room}', false);
		$form->setOutputScreen ();
		if ($result = $form->process ($html)) {
			
			# Insert into the database
			$this->databaseConnection->insert ($this->settings['database'], $this->settings['table'], $result);
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Template
	private function template ()
	{
		return $html = '
			<h1>Room Risk Assessment</h1>
			
			<div class="graybox">
				<p>Please use this document to demonstrate that you have inspected your room and noted potential Safety Hazards.</p>
				<p>Please report to the Departmental Safety Officer any items you think are unsafe as soon as possible.</p>
			</div>
			
			<table class="lines">
				<tbody>
					<tr>
						<td>Room number:</td>
						<td>{room}</td>
					</tr>
					<tr>
						<td>Building:</td>
						<td>{building}</td>
					</tr>
					<tr>
						<td>Room function:</td>
						<td>{function}</td>
					</tr>
					<tr>
						<td>Present occupier(s):</td>
						<td>{occupiers}</td>
					</tr>
				</tbody>
			</table>
			
			<table class="lines">
				<tbody>
					<tr>
						<th>Potential hazard</th>
						<th>Observations</th>
						<th>Comments</th>
					</tr>
					<tr>
						<td>Is the room tidy and free from trip and fire hazards? E.g. trailing cables or piles of flammable papers?</td>
						<td>{tidy}</td>
						<td>{tidyInfo}</td>
					</tr>
					<tr>
						<td>Is a fire escape direction sign in view outside the room?</td>
						<td>{fireEscapeSign}</td>
						<td>{fireEscapeSignInfo}</td>
					</tr>
					<tr>
						<td>Has a display screen self assessment checklist been completed recently by the occupier/s?</td>
						<td>{vdu}</td>
						<td>{vduInfo}</td>
					</tr>
					<tr>
						<td>Do room occupants need special considerations? E.g. Expectant mothers, partially sighted or hearing impairment.</td>
						<td>{specialConsiderations}</td>
						<td>{specialConsiderationsInfo}</td>
					</tr>
					<tr>
						<td>Are there any items stored on high shelves and, if needed, do you have access to a stool or ladder?</td>
						<td>{highShelves}</td>
						<td>{highShelvesInfo}</td>
					</tr>
					<tr>
						<td>Are all shelves securely fixed to the brackets?</td>
						<td>{shelvesFixed}</td>
						<td>{shelvesFixedInfo}</td>
					</tr>
					<tr>
						<td>Do you consider that the furniture you are using is suitable? Please comment on how it could be improved.</td>
						<td>{furniture}</td>
						<td>{furnitureInfo}</td>
					</tr>
					<tr>
						<td>Do occupants have any additional requirements such as workwear or PPE?</td>
						<td>{ppe}</td>
						<td>{ppeInfo}</td>
					</tr>
					<tr>
						<td>Please list chemicals stored in this room with a hazard label e.g. Tippex, plant food, cleaning solvents.</td>
						<td colspan="2">{chemicals}</td>
					</tr>
					<tr>
						<td>Additional comments or suggestions:	</td>
						<td colspan="2">{comments}</td>
					</tr>
				</tbody>
			</table>
			
			<p>Name: {name}</p>
			<p>E-mail: {email}</p>
		';
	}
	
	
	# Admin export facility
	public function download ()
	{
		# Compile the HTML
		$html  = "\n<p>In this section, you can download a spreadsheet of all submissions.</p>";
		$html .= "\n<p><a class=\"actions\" href=\"{$this->baseUrl}/download/roomassessments.csv\"><img src=\"/images/icons/page_excel.png\" alt=\"\" border=\"0\" /> Export data</a></p>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# CSV download
	public function downloadcsv ()
	{
		# Get the data
		$data = $this->databaseConnection->select ($this->settings['database'], $this->settings['table']);
		
		# Serve as CSV
		require_once ('csv.php');
		csv::serve ($data, 'roomassessments');
	}
}

?>
