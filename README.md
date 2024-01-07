# 4vGYM Symfony API Project

## Introduction
The main aspects we worked on in this project were:

- Creating controllers
- Persisting information in a relational model
- Creating relationships between entities and bringing them to the relational model (M --> 1 and N-->M)

## API Specifications
We were asked to create a REST API for our 4vGYM. This REST API has the following specifications:

### /activity-types
GET: Returns the list of Activity types. Each activity consists of an ID, name, and the number of monitors needed to perform it.

### /monitors
GET: Returns the list of monitors ID, Name, Email, Phone, and Photo.
POST: Allows creating new monitors and returns the JSON with the new monitor's information.
PUT: Allows editing existing monitors.
DELETE: Allows deleting monitors.

### /activities
GET: Returns the list of Activities, with all the information about the types, included monitors, and the date. Allows searching using a date parameter that will have a dd-MM-yyyy format.
POST: Allows creating new activities and returns the information of the new activity. It must be validated that the new activity has the monitors required by the activity type. The date and duration are not free fields. Only 90-minute classes that start at 09:00, 13:30, and 17:30 are admitted.
PUT: Allows editing existing activities.
DELETE: Allows deleting activities.

All the API must have validation of the POST fields.

## Database
Therefore, it is inferred that the supporting database contains the following:

- Monitors Table.
- Activity Types Table.
- Activities Table. (FK on Activity Types)
- Activities-Monitors Table (N-M)
