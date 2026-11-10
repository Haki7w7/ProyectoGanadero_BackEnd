using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace GuateGanado.Api.Models;

/// <summary>
/// Entidad del contrato comparativo (Hito 0): 7 atributos y una relacion (Potrero).
/// </summary>
[Table("animales")]
public class Animal
{
    [Column("id")]
    public int Id { get; set; }

    [Column("numero_arete")]
    [Required, MaxLength(50)]
    public string NumeroArete { get; set; } = string.Empty;

    [Column("raza")]
    [Required, MaxLength(80)]
    public string Raza { get; set; } = string.Empty;

    [Column("sexo")]
    [Required, MaxLength(10)]
    public string Sexo { get; set; } = string.Empty;

    [Column("fecha_nacimiento")]
    [Required]
    public DateOnly FechaNacimiento { get; set; }

    [Column("estado")]
    [Required, MaxLength(20)]
    public string Estado { get; set; } = string.Empty;

    [Column("potrero_id")]
    public int PotreroId { get; set; }

    public Potrero? Potrero { get; set; }
}
