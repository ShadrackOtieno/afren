<?php
return [
    'org_types' => ['NGO / Non-profit','Government agency','University / Research institute','International organization','Private company','Consultancy / Research firm','Foundation / Donor','Other'],
    'sectors' => ['Health','Education','Agriculture & Food Security','Economics & Finance','Governance & Politics','Gender & Social Inclusion','Environment & Climate','WASH','Energy','Technology & Digital','Humanitarian','Market Research','Other'],
    'education_levels' => ['none'=>0,'certificate'=>1,'diploma'=>2,'bachelor'=>3,'master'=>4,'phd'=>5],
    'categories' => ['researcher'=>'Researcher','enumerator'=>'Enumerator','both'=>'Researcher & Enumerator'],
    'arrangements' => ['Recruit only (introduce candidates)','Recruit and manage contracting','Full fieldwork team management','Other / to be discussed'],
    'request_statuses' => ['new'=>'New','under_review'=>'Under review','invoiced'=>'Invoiced','paid'=>'Paid / In progress','shortlisting'=>'Shortlisting','completed'=>'Completed','declined'=>'Declined'],
    'renewal_notice_days' => 7,
    'grace_days' => 7,
    'invoice_due_days' => 14,
];
