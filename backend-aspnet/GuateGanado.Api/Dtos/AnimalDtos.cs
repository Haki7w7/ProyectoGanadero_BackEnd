using System.ComponentModel.DataAnnotations;

namespace GuateGanado.Api.Dtos;

/// <summary>Carga util de creacion. Las anotaciones producen el 422 por campo.</summary>
public class AnimalCreateDto
{
    [Required(ErrorMessage = "El numero de arete es obligatorio.")]
    [MaxLength(50)]
    public string NumeroArete { get; set; } = string.Empty;

    [Required(ErrorMessage = "La raza es obligatoria.")]
    [MaxLength(80)]
    public string Raza { get; set; } = string.Empty;

    [Required(ErrorMessage = "El sexo es obligatorio.")]
    [RegularExpression("^(Macho|Hembra)$", ErrorMessage = "El sexo debe ser Macho o Hembra.")]
    public string Sexo { get; set; } = string.Empty;

    [Required(ErrorMessage = "La fecha de nacimiento es obligatoria.")]
    public DateOnly FechaNacimiento { get; set; }

    [Required(ErrorMessage = "El estado es obligatorio.")]
    [RegularExpression("^(Activo|Vendido|Enfermo|Fallecido)$",
        ErrorMessage = "El estado debe ser Activo, Vendido, Enfermo o Fallecido.")]
    public string Estado { get; set; } = string.Empty;

    [Range(1, int.MaxValue, ErrorMessage = "El potrero_id debe ser mayor que cero.")]
    public int PotreroId { get; set; }
}

public class AnimalUpdateDto : AnimalCreateDto { }

public class AnimalResponseDto
{
    public int Id { get; set; }
    public string NumeroArete { get; set; } = string.Empty;
    public string Raza { get; set; } = string.Empty;
    public string Sexo { get; set; } = string.Empty;
    public DateOnly FechaNacimiento { get; set; }
    public string Estado { get; set; } = string.Empty;
    public int PotreroId { get; set; }
    public string? PotreroNombre { get; set; }
}

/// <summary>Sobre de paginacion equivalente al de Laravel y NestJS.</summary>
public class PagedResult<T>
{
    public IEnumerable<T> Data { get; set; } = Array.Empty<T>();
    public PageMeta Meta { get; set; } = new();
}

public class PageMeta
{
    public int CurrentPage { get; set; }
    public int PerPage { get; set; }
    public int Total { get; set; }
    public int LastPage { get; set; }
}
