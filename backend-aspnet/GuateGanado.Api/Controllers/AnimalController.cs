using GuateGanado.Api.Data;
using GuateGanado.Api.Dtos;
using GuateGanado.Api.Models;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace GuateGanado.Api.Controllers;

[ApiController]
[Route("api/v1/animal")]
[Authorize]
public class AnimalController : ControllerBase
{
    private readonly AppDbContext _db;
    public AnimalController(AppDbContext db) => _db = db;

    private static AnimalResponseDto ToDto(Animal a) => new()
    {
        Id = a.Id,
        NumeroArete = a.NumeroArete,
        Raza = a.Raza,
        Sexo = a.Sexo,
        FechaNacimiento = a.FechaNacimiento,
        Estado = a.Estado,
        PotreroId = a.PotreroId,
        PotreroNombre = a.Potrero?.Nombre
    };

    /// <summary>GET /api/v1/animal -> 200 con datos y metadatos; 401 sin token.</summary>
    [HttpGet]
    [ProducesResponseType(typeof(PagedResult<AnimalResponseDto>), StatusCodes.Status200OK)]
    public async Task<IActionResult> Listar(
        [FromQuery] int page = 1,
        [FromQuery(Name = "per_page")] int perPage = 15,
        [FromQuery] string? arete = null,
        [FromQuery] string? q = null)
    {
        page = page < 1 ? 1 : page;
        perPage = perPage is < 1 or > 100 ? 15 : perPage;

        var consulta = _db.Animales.AsNoTracking().Include(a => a.Potrero).AsQueryable();

        if (!string.IsNullOrWhiteSpace(arete))
            consulta = consulta.Where(a => a.NumeroArete == arete);

        if (!string.IsNullOrWhiteSpace(q))
            consulta = consulta.Where(a => a.NumeroArete.Contains(q) || a.Raza.Contains(q));

        var total = await consulta.CountAsync();

        var items = await consulta
            .OrderBy(a => a.Id)
            .Skip((page - 1) * perPage)
            .Take(perPage)
            .Select(a => ToDto(a))
            .ToListAsync();

        return Ok(new PagedResult<AnimalResponseDto>
        {
            Data = items,
            Meta = new PageMeta
            {
                CurrentPage = page,
                PerPage = perPage,
                Total = total,
                LastPage = total == 0 ? 1 : (int)Math.Ceiling(total / (double)perPage)
            }
        });
    }

    /// <summary>GET /api/v1/animal/{id} -> 200; 404 si no existe.</summary>
    [HttpGet("{id:int}")]
    [ProducesResponseType(typeof(AnimalResponseDto), StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<IActionResult> Detalle(int id)
    {
        var animal = await _db.Animales.AsNoTracking()
            .Include(a => a.Potrero)
            .FirstOrDefaultAsync(a => a.Id == id);

        if (animal is null)
            return NotFound(new { message = $"No existe un animal con id {id}." });

        return Ok(ToDto(animal));
    }

    /// <summary>POST /api/v1/animal -> 201 con encabezado Location; 422 datos invalidos.</summary>
    [HttpPost]
    [ProducesResponseType(typeof(AnimalResponseDto), StatusCodes.Status201Created)]
    [ProducesResponseType(StatusCodes.Status422UnprocessableEntity)]
    public async Task<IActionResult> Crear([FromBody] AnimalCreateDto dto)
    {
        if (await _db.Animales.AnyAsync(a => a.NumeroArete == dto.NumeroArete))
        {
            ModelState.AddModelError(nameof(dto.NumeroArete), "El numero de arete ya esta registrado.");
            return UnprocessableEntity(new ValidationProblemDetails(ModelState));
        }

        if (!await _db.Potreros.AnyAsync(p => p.Id == dto.PotreroId))
        {
            ModelState.AddModelError(nameof(dto.PotreroId), "El potrero indicado no existe.");
            return UnprocessableEntity(new ValidationProblemDetails(ModelState));
        }

        var animal = new Animal
        {
            NumeroArete = dto.NumeroArete,
            Raza = dto.Raza,
            Sexo = dto.Sexo,
            FechaNacimiento = dto.FechaNacimiento,
            Estado = dto.Estado,
            PotreroId = dto.PotreroId
        };

        _db.Animales.Add(animal);
        await _db.SaveChangesAsync();

        return CreatedAtAction(nameof(Detalle), new { id = animal.Id }, ToDto(animal));
    }

    /// <summary>PUT /api/v1/animal/{id} -> 200; 404 si no existe; 422 datos invalidos.</summary>
    [HttpPut("{id:int}")]
    [ProducesResponseType(typeof(AnimalResponseDto), StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    [ProducesResponseType(StatusCodes.Status422UnprocessableEntity)]
    public async Task<IActionResult> Actualizar(int id, [FromBody] AnimalUpdateDto dto)
    {
        var animal = await _db.Animales.FirstOrDefaultAsync(a => a.Id == id);
        if (animal is null)
            return NotFound(new { message = $"No existe un animal con id {id}." });

        if (await _db.Animales.AnyAsync(a => a.NumeroArete == dto.NumeroArete && a.Id != id))
        {
            ModelState.AddModelError(nameof(dto.NumeroArete), "El numero de arete ya esta registrado.");
            return UnprocessableEntity(new ValidationProblemDetails(ModelState));
        }

        if (!await _db.Potreros.AnyAsync(p => p.Id == dto.PotreroId))
        {
            ModelState.AddModelError(nameof(dto.PotreroId), "El potrero indicado no existe.");
            return UnprocessableEntity(new ValidationProblemDetails(ModelState));
        }

        animal.NumeroArete = dto.NumeroArete;
        animal.Raza = dto.Raza;
        animal.Sexo = dto.Sexo;
        animal.FechaNacimiento = dto.FechaNacimiento;
        animal.Estado = dto.Estado;
        animal.PotreroId = dto.PotreroId;

        await _db.SaveChangesAsync();
        await _db.Entry(animal).Reference(a => a.Potrero).LoadAsync();

        return Ok(ToDto(animal));
    }

    /// <summary>DELETE /api/v1/animal/{id} -> 204; 404 si no existe; 409 si tiene dependencias.</summary>
    [HttpDelete("{id:int}")]
    [ProducesResponseType(StatusCodes.Status204NoContent)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    [ProducesResponseType(StatusCodes.Status409Conflict)]
    public async Task<IActionResult> Eliminar(int id)
    {
        var animal = await _db.Animales.FirstOrDefaultAsync(a => a.Id == id);
        if (animal is null)
            return NotFound(new { message = $"No existe un animal con id {id}." });

        // Regla del contrato: un animal vendido conserva trazabilidad y no se elimina.
        if (animal.Estado == "Vendido")
            return Conflict(new { message = "El animal tiene dependencias de trazabilidad y no puede eliminarse." });

        _db.Animales.Remove(animal);
        await _db.SaveChangesAsync();

        return NoContent();
    }
}
