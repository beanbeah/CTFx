## Deprecation Notice
This platform is no longer maintained (and has not been for a while). This platform is succeeded by [dunhack.me](https://github.com/ItzyBitzySpider/dunhack.me)

# CTFx

CTFx is a CTF Platform forked from [mellivora](https://github.com/Nakiami/mellivora), that focuses on low memory footprint and low server CPU usage. A jeopardy CTF platform with a futuristic interface that's optimized for slower hardware, meaning that there is no bulky Javascript running in the background, nor length CSS stylesheets. CTFx improves on mellivora with the addition of new features and a revamped UI. 

![](https://i.imgur.com/PpCMNPy.png)


## Features 

- Unlimited categories and challenges with configurable dynamic/static scoring
- Challenge hints
- Set custom start and end times for any challenge or category
- Unlockable challenges (In order to see them requires you to solve another challenge (from any category you choose))
- Local or [Amazon S3](https://aws.amazon.com/s3/) or Amazon S3 Compatible Storage challenge file upload
- Optional automatic MD5 append to files.
- Admin Panel with competition overview, IP logging, user/email search, exception log (that includes the users that caused them)
- Optional signup restrictions based on email regex
- Create/edit front page news
- Arbitrary menu items and internal pages
- Markdown Support for challenge and category descriptions, news, etc ...
- Optional solve count limit per challenge
- [reCAPTCHA](https://www.google.com/recaptcha/) support
- User-defined or auto-generated passwords on signup
- Configurable caching
- Caching proxy (like [Cloudflare](https://www.cloudflare.com/)) aware (optional x-forwarded-for trust)
- [Segment](https://segment.com/) analytics support
- SMTP email support. Bulk or single email composition
- TOTP two factor auth support
- [CTF Time](https://ctftime.org/) compatible JSON scoreboard
- Self-serve and admin password reset.
- **Scalable Platform**
- And more ...

## Scaling

CTFx scales well on Amazon Elastic Beanstalk and Digital Ocean. There is also support for Amazon S3 file storage and other S3 compatible storage. 

## Performance

CTFx is lightweight and suprisingly really really really fast. See [benchmarks.md](benchmarks.md) for some shocking benchmarks. 

## Installation

Standard Deployment: [Standard](standard.md)

Cluster Deployment: [Cluster](cluster.md)

Docker Default Admin Credentials: `admin@admin.com:Password1.`

## License
This software is licenced under the [GNU General Public License v3 (GPL-3)](http://www.tldrlegal.com/license/gnu-general-public-license-v3-%28gpl-3%29). The "include/thirdparty/" directory contains third party code. Please read their LICENSE files for information on the software availability and distribution.
