CREATE TABLE `occlassextraparameters` (  
  `class_identifier` varchar(100) NOT NULL,
  `attribute_identifier` varchar(100) NOT NULL,
  `handler` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext,
  `created_time` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
