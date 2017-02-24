# Kills all running containers of an image and then removes them.
cleanAll () {
  if [[ -z $ENVIRONMENT ]]; then
    ENVIRONMENT="debug"
  fi

  composeFileName="docker-compose.yml"
  overrideFileName="docker-compose.$ENVIRONMENT.yml"  

  if [[ ! -f $composeFileName ]]; then
    echo "File '$composeFileName' does not exist."
  if [[ ! -f $overrideFileName ]]; then
    echo "$ENVIRONMENT is not a valid parameter. File '$ovverrideFileName' does not exist."
  else
    docker-compose -f "$composeFileName" -f "$overrideFileName" down --rmi all
    docker rmi $(docker images --filter "dangling=true" -q)
  fi
}

# Builds the Docker image.
buildImage () {
  if [[ -z $ENVIRONMENT ]]; then
    ENVIRONMENT="debug"
  fi

  composeFileName="docker-compose.yml"
  overrideFileName="docker-compose.$ENVIRONMENT.yml"

  if [[ ! -f $composeFileName ]]; then
    echo "File '$composeFileName' does not exist."
  if [[ ! -f $overrideFileName ]]; then
    echo "$ENVIRONMENT is not a valid parameter. File '$ovverrideFileName' does not exist."
  else
    docker-compose -f "$composeFileName" -f "$overrideFileName" build
    docker rmi $(docker images --filter "dangling=true" -q)
  fi
}

# Runs docker-compose.
compose () {
  if [[ -z $ENVIRONMENT ]]; then
    ENVIRONMENT="debug"
  fi

  composeFileName="docker-compose.yml"
  overrideFileName="docker-compose.$ENVIRONMENT.yml"

  if [[ ! -f $composeFileName ]]; then
    echo "File '$composeFileName' does not exist."
  if [[ ! -f $overrideFileName ]]; then
    echo "$ENVIRONMENT is not a valid parameter. File '$ovverrideFileName' does not exist."
  else
    docker-compose -f "$composeFileName" -f "$overrideFileName" kill
    docker-compose -f "$composeFileName" -f "$overrideFileName" up -d --remove-orphans
  fi
}

# Shows the usage for the script.
showUsage () {
  echo "Usage: dockerTask.sh [COMMAND] (ENVIRONMENT)"
  echo "    Runs build or compose using specific environment (if not provided, debug environment is used)"
  echo ""
  echo "Commands:"
  echo "    build: Builds a Docker image."
  echo "    compose: Runs docker-compose."
  echo "    clean: Removes the image and kills all containers based on that image."
  echo ""
  echo "Environments:"
  echo "    debug: Uses debug environment."
  echo "    release: Uses release environment."
  echo ""
  echo "Example:"
  echo "    ./dockerTask.sh build debug"
  echo ""
  echo "    This will:"
  echo "        Build a Docker image using debug environment."
}

if [ $# -eq 0 ]; then
  showUsage
else
  case "$1" in
    "compose")
            ENVIRONMENT=$(echo $2 | tr "[:upper:]" "[:lower:]")
            buildImage
            compose
            ;;
    "build")
            ENVIRONMENT=$(echo $2 | tr "[:upper:]" "[:lower:]")
            buildImage
            ;;
    "clean")
            ENVIRONMENT=$(echo $2 | tr "[:upper:]" "[:lower:]")
            cleanAll
            ;;
    *)
            showUsage
            ;;
  esac
fi