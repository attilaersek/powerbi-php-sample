<#
.SYNOPSIS
Builds and runs a Docker image.
.PARAMETER Compose
Builds images and runs docker-compose up. Kills old.
.PARAMETER Build
Builds images.
.PARAMETER Clean
Runs docker-compose down, and removes images.
.PARAMETER Environment
The enviorment to build for (Debug or Release), defaults to Debug
.EXAMPLE
C:\PS> .\dockerTask.ps1 -Build
Build a Docker image named test
#>

Param(
    [Parameter(Mandatory=$True,ParameterSetName="Compose")]
    [switch]$Compose,
    [Parameter(Mandatory=$True,ParameterSetName="Build")]
    [switch]$Build,
    [Parameter(Mandatory=$True,ParameterSetName="Clean")]
    [switch]$Clean,
    [parameter(ParameterSetName="Compose")]
    [parameter(ParameterSetName="Build")]
    [parameter(ParameterSetName="Clean")]
    [ValidateNotNullOrEmpty()]
    [String]$Environment = "Debug"
)

# Kills all running containers of an image and then removes them.
function CleanAll () {
    $composeFileName = "docker-compose.yml"
    $overrideFileName = "docker-compose.$Environment.yml"

    if (Test-Path $composeFileName) {
        if (Test-Path $overrideFileName) {
            docker-compose -f "$composeFileName" -f "$overrideFileName" down --rmi all
            docker rmi $(docker images --filter "dangling=true" -q)
        }
        else {
            Write-Error -Message "$Environment is not a valid parameter. File '$overrideFileName' does not exist." -Category InvalidArgument                
        }
    }
    else {
        Write-Error -Message "File '$composeFileName' does not exist." -Category InvalidArgument
    }
}

# Builds the Docker image.
function BuildImage () {
    $composeFileName = "docker-compose.yml"
    $overrideFileName = "docker-compose.$Environment.yml"

    if (Test-Path $composeFileName) {
        if (Test-Path $overrideFileName) {
            docker-compose -f "$composeFileName" -f "$overrideFileName" build
            docker rmi $(docker images --filter "dangling=true" -q)
        }
        else {
            Write-Error -Message "$Environment is not a valid parameter. File '$overrideFileName' does not exist." -Category InvalidArgument            
        }
    }
    else {
        Write-Error -Message "File '$composeFileName' does not exist." -Category InvalidArgument
    }
}

# Runs docker-compose.
function Compose () {
    $composeFileName = "docker-compose.yml"
    $overrideFileName = "docker-compose.$Environment.yml"

    if (Test-Path $composeFileName) {
        if (Test-Path $overrideFileName) {
            docker-compose -f $composeFileName -f $overrideFileName kill
            docker-compose -f $composeFileName -f $overrideFileName up -d --remove-orphans
        }
        else {
            Write-Error -Message "$Environment is not a valid parameter. File '$overrideFileName' does not exist." -Category InvalidArgument
        }
    }
    else {
        Write-Error -Message "File '$composeFileName' does not exist." -Category InvalidArgument
    }
}

$Environment = $Environment.ToLowerInvariant()

# Call the correct function for the parameter that was used
if($Compose) {
    BuildImage
    Compose
}
elseif($Build) {
    BuildImage
}
elseif ($Clean) {
    CleanAll
}