FROM egob/simple:v1.5


RUN echo "deb http://packages.dotdeb.org wheezy-php56 all" >> /etc/apt/sources.list ; \
    echo "deb-src http://packages.dotdeb.org wheezy-php56 all " >> /etc/apt/sources.list 

RUN apt-get update

RUN apt-get install -y wget ; \
    wget http://www.dotdeb.org/dotdeb.gpg -O- | apt-key add - 


RUN apt-get install -y -f  --force-yes libapache2-mod-php5  php5 php5-fpm 

