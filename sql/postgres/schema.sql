CREATE TABLE occlassextraparameters (
  class_identifier VARCHAR(100) DEFAULT NULL NOT NULL,
  attribute_identifier VARCHAR(100) DEFAULT NULL NOT NULL,
  handler VARCHAR(100) DEFAULT NULL NOT NULL,
  key VARCHAR(100) DEFAULT NULL NOT NULL,
  value text,
  created_time INTEGER DEFAULT 0
);
