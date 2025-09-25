# Network Configuration
# VPC Network
resource "google_compute_network" "smart_gallery_network" {
  name                    = "${local.name_prefix}-network"
  auto_create_subnetworks = false
  
  timeouts {
    create = "5m"
    delete = "5m"
  }
}

# Subnet
resource "google_compute_subnetwork" "smart_gallery_subnet" {
  name          = "${local.name_prefix}-subnet"
  ip_cidr_range = "10.0.0.0/24"
  network       = google_compute_network.smart_gallery_network.id
  region        = var.region
}

# Static IP for the VM
resource "google_compute_address" "smart_gallery_ip" {
  name   = "${local.name_prefix}-ip"
  region = var.region
}

# Firewall Rules
resource "google_compute_firewall" "smart_gallery_ssh" {
  name    = "${local.name_prefix}-allow-ssh"
  network = google_compute_network.smart_gallery_network.name

  allow {
    protocol = "tcp"
    ports    = ["22"]
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["smart-gallery-server"]
}

resource "google_compute_firewall" "smart_gallery_http" {
  name    = "${local.name_prefix}-allow-http"
  network = google_compute_network.smart_gallery_network.name

  allow {
    protocol = "tcp"
    ports    = ["80"]
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["smart-gallery-server"]
}

resource "google_compute_firewall" "smart_gallery_https" {
  name    = "${local.name_prefix}-allow-https"
  network = google_compute_network.smart_gallery_network.name

  allow {
    protocol = "tcp"
    ports    = ["443"]
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["smart-gallery-server"]
}

resource "google_compute_firewall" "smart_gallery_icmp" {
  name    = "${local.name_prefix}-allow-icmp"
  network = google_compute_network.smart_gallery_network.name

  allow {
    protocol = "icmp"
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["smart-gallery-server"]
}