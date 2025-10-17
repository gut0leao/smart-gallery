# Smart Gallery - Terraform Infrastructure
# Provisiona VM + Rede + Firewall + DNS no GCP (Free Tier)

terraform {
  required_version = ">= 1.0"
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 5.0"
    }
  }
  
  # Enable state management for resource updates
  backend "local" {
    path = "terraform.tfstate"
  }
}

# Variables
variable "project_id" {
  description = "GCP Project ID"
  type        = string
}

variable "region" {
  description = "GCP Region"
  type        = string
  default     = "us-central1"
}

variable "zone" {
  description = "GCP Zone"
  type        = string
  default     = "us-central1-a"
}

variable "domain_name" {
  description = "Domain name for the site"
  type        = string
}

variable "environment" {
  description = "Environment (staging/production)"
  type        = string
  default     = "staging"
}

variable "machine_type" {
  description = "GCP VM machine type"
  type        = string
}

variable "ssh_public_key" {
  description = "SSH public key for VM access"
  type        = string
  default     = ""
  default     = "e2-micro" # Free tier eligible
}

# Provider configuration
provider "google" {
  project = var.project_id
  region  = var.region
  zone    = var.zone
}

# Local values
locals {
  name_prefix = "smart-gallery-${var.environment}"
  
  labels = {
    project     = "smart-gallery"
    environment = var.environment
    managed_by  = "terraform"
  }
}