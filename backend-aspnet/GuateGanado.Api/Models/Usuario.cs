using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace GuateGanado.Api.Models;

[Table("usuarios")]
public class Usuario
{
    [Column("id")]
    public int Id { get; set; }

    [Column("email")]
    [Required, MaxLength(120)]
    public string Email { get; set; } = string.Empty;

    [Column("password_hash")]
    [Required]
    public string PasswordHash { get; set; } = string.Empty;

    [Column("rol")]
    [Required, MaxLength(30)]
    public string Rol { get; set; } = "operario";
}
