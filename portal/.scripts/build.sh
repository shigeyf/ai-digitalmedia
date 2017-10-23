#!/usr/bin/env bash
set -x -e

cwd=`dirname "$0"`
expr "$0" : "/.*" > /dev/null || cwd=`(cd "$cwd" && pwd)`

if [ $# -ne 3 ]
then
    echo "$0 [username] [dockerhub account] [dockerhub password]"
    exit
fi
echo "username=$1"
echo "docker account name=$2"
echo "docker account passwd=$3"

docker login -u "$2" -p "$3"

version=`cat $cwd/../VERSION`
tag="$version"
echo "tag=$tag"
docker build -t "$1"/ai-digitalmedia-portal:$tag $cwd/..
docker push "$1"/ai-digitalmedia-portal:$tag

docker logout
