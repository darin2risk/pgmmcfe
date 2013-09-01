pgmmcfe
=======

Postgres / mmcFE based mining pool software.

	-PostgreSQL Database
	-Downed Worker Notification
	-Time Zone Support
	-Uptime Graphs, Individual and Pool
	-Auto or Manual Payouts
	-Accounting Grade Cross-Method Database Block tracking
	-Idempotent Backend Jobs
	-Variable Reward Support
	-Adjustable PPLNS Window Support
	-Variable "Confirm Count" Support
	-Variable "Transaction Fee" Support
	-Easily add new "Tickers"
	-Adjustable Job Scheduling

Development
-----------

**pgmmcfe** is a fork / rewrite of mmcfe by Greedi & g2x3k

I liked the mmcfe frontend, but the backend just wasn't cutting it for a coins with a short solve time. Also, I'm a PostgreSQL guy, and I couldn't find anything available written for postgres at the time. I have a feeling mmcfe-ng would port fairly well, but ... I had already been moving down this road when it came out. So, here's some pool software that runs with Postgres, can handle short solve times, and has a few other features I found desireable. 