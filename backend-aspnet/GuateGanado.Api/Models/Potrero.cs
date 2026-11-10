using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace GuateGanado.Api.Models;

[Table("potreros")]
public class Potrero
{
    [Column("id")]
    public int Id { get; set; }

    [Column("nombre")]
    [Required, MaxLength(100)]
    public string Nombre { get; set; } = string.Empty;

    public ICollection<Animal> Animales { get; set; } = new List<Animal>();
}
