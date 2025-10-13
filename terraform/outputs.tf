# Outputs
output "vm_name" {
  description = "Name of the created VM instance"
  value       = google_compute_instance.smart_gallery_vm.name
}

output "vm_zone" {
  description = "Zone of the VM instance"
  value       = google_compute_instance.smart_gallery_vm.zone
}

output "vm_external_ip" {
  description = "External IP of the VM instance"
  value       = google_compute_address.smart_gallery_ip.address
}

output "vm_internal_ip" {
  description = "Internal IP of the VM instance"
  value       = google_compute_instance.smart_gallery_vm.network_interface[0].network_ip
}

output "network_name" {
  description = "Name of the created network"
  value       = google_compute_network.smart_gallery_network.name
}

output "subnet_name" {
  description = "Name of the created subnet"
  value       = google_compute_subnetwork.smart_gallery_subnet.name
}

output "site_url" {
  description = "Site URL (HTTPS)"
  value       = "https://${var.domain_name}"
}

output "firewall_rules" {
  description = "Created firewall rules"
  value = [
    google_compute_firewall.smart_gallery_ssh.name,
    google_compute_firewall.smart_gallery_http.name,
    google_compute_firewall.smart_gallery_https.name,
    google_compute_firewall.smart_gallery_icmp.name
  ]
}