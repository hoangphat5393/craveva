# Routes

- Generated at: 2026-05-04T05:35:06+00:00

- Modules/Recruit/Routes/api.php (lines=?, methods=?)
- Modules/Recruit/Routes/web.php (lines=?, methods=?)

## Route samples

### Modules/Recruit/Routes/api.php


### Modules/Recruit/Routes/web.php

- delete /interview-rounds/{id}/delete
- post /jobOffer-accept/{id}
- get /jobOffer/{hash}/{company?}
- get /thankyou/{slug?}
- get accept-offer/{id}
- resource applicant-note
- resource application-file
- get application-file/download/{id}
- post apply-quick-action
- resource candidate-database
- resource candidate-follow-up
- post candidate-follow-up/change-follow-up-status
- get careers/{slug?}
- post change-status
- get create-designation
- get create-employee/{id}
- resource custom-question-settings
- post custom-question-settings/change-status
- post employee-response
- post employee-store
- resource evaluation
- get fetch-applications
- get fetch-component
- get fetched-currency
- resource footer-settings
- post footer-settings/change-status/{id}
- get get-salary
- get getJobSubCategories/{id}
- get import
- post import
- get import/download-sample-csv
- post import/process
- resource interview-files
- get interview-files/download/{id}
- resource interview-schedule
- resource interview-stages
- post job-alert-save
- get job-alert-unsubscribe/{slug?}/{alertHash?}
- get job-alert/{slug?}
- resource job-appboard
- post job-appboard/add-skills
- post job-appboard/add-status
- post job-appboard/application-remark-store
- get job-appboard/application-remark/{id}/{board?}
- post job-appboard/collapseColumn
- get job-appboard/fetch-status-model-label
- post job-appboard/interview-store
- get job-appboard/interview/{id}/{board?}
- get job-appboard/loadMore
- post job-appboard/offer-letter-store
- get job-appboard/offer-letter/{id}/{board?}
- post job-appboard/rejected-remark-store
- get job-appboard/rejected-remark/{id}/{board?}
- post job-appboard/store-status
- post job-appboard/updateIndex
- get job-application/location
- resource job-applications
- post job-applications/apply-quick-action
- post job-applications/change-status
- get job-applications/get_custom_fields
